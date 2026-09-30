<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use App\Models\ProctoringLog;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExamController extends Controller
{
    /**
     * Get all subjects for a given grade
     */
    public function subjects(Request $request): JsonResponse
    {
        $grade = $request->query('grade');

        $query = Subject::withCount('questions');
        if ($grade && $grade !== 'all') {
            $query->where('grade', (int)$grade);
        }

        $subjects = $query->get()->map(function ($s) {
            return [
                'id'               => $s->id,
                'title'            => $s->title,
                'grade'            => $s->grade,
                'duration_minutes' => $s->duration_minutes,
                'max_violations'   => $s->max_violations ?? 3,
                'remedy_code'      => $s->remedy_code ?? ('REMEDI' . $s->grade),
                'icon_name'        => $s->icon_name ?? 'school',
                'description'      => $s->description ?? '',
                'question_count'   => $s->questions_count,
            ];
        });

        return response()->json([
            'success'  => true,
            'grade'    => $grade ? (int)$grade : null,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Get questions for a subject
     */
    public function questions(Request $request, int $subjectId): JsonResponse
    {
        $subject = Subject::findOrFail($subjectId);

        $limit = $request->query('limit', 20);

        $questions = Question::where('subject_id', $subjectId)
            ->inRandomOrder()
            ->limit((int)$limit)
            ->get()
            ->map(function ($q) {
                return [
                    'id'                   => $q->id,
                    'prompt'               => $q->prompt,
                    'question_image'       => $q->question_image,
                    'options'              => is_array($q->options) ? $q->options : json_decode($q->options, true),
                    'option_images'        => is_array($q->option_images) ? $q->option_images : json_decode($q->option_images, true),
                    'correct_answer_index' => $q->correct_answer_index,
                    'explanation'          => $q->explanation ?? '',
                ];
            });

        return response()->json([
            'success'  => true,
            'subject'  => [
                'id'               => $subject->id,
                'title'            => $subject->title,
                'grade'            => $subject->grade,
                'duration_minutes' => $subject->duration_minutes,
                'max_violations'   => $subject->max_violations ?? 3,
                'remedy_code'      => $subject->remedy_code ?? ('REMEDI' . $subject->grade),
            ],
            'questions' => $questions,
        ]);
    }

    /**
     * Verify remedy code to reset proctoring violations
     */
    public function verifyRemedy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|integer|exists:subjects,id',
            'code'       => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak lengkap',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $subject = Subject::findOrFail($request->subject_id);
        $inputCode = strtoupper(trim($request->code));
        $correctCode = strtoupper(trim($subject->remedy_code));

        if ($inputCode === $correctCode) {
            return response()->json([
                'success' => true,
                'message' => 'Kode remedi valid! Pelanggaran telah direset.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode remedi salah! Silakan tanyakan kode kepada pengawas ujian.',
        ], 403);
    }

    /**
     * Submit an exam result
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_id'          => 'required|integer|exists:subjects,id',
            'grade'               => 'required|integer|min:1|max:6',
            'score'               => 'required|integer|min:0|max:100',
            'correct_count'       => 'required|integer',
            'wrong_count'         => 'required|integer',
            'unanswered_count'    => 'required|integer',
            'total_questions'     => 'required|integer',
            'time_spent_seconds'  => 'required|integer',
            'user_answers'        => 'nullable|array',
            'proctoring_summary'  => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $userId = 1;
        try {
            if ($user = JWTAuth::parseToken()->authenticate()) {
                $userId = $user->id;
            }
        } catch (\Exception $e) {
            // Fallback for direct student submission
            if ($request->filled('username')) {
                $found = User::where('username', $request->input('username'))->first();
                if ($found) {
                    $userId = $found->id;
                }
            }
        }

        $result = ExamResult::create([
            'user_id'            => $userId,
            'subject_id'         => $request->subject_id,
            'grade'              => $request->grade,
            'score'              => $request->score,
            'correct_count'      => $request->correct_count,
            'wrong_count'        => $request->wrong_count,
            'unanswered_count'   => $request->unanswered_count,
            'total_questions'    => $request->total_questions,
            'time_spent_seconds' => $request->time_spent_seconds,
            'user_answers'       => json_encode($request->user_answers ?? []),
            'proctoring_summary' => json_encode($request->proctoring_summary ?? []),
        ]);

        // Auto-record proctoring violations into proctoring_logs linked to exam_result_id
        if ($request->filled('proctoring_summary') && is_array($request->proctoring_summary)) {
            foreach ($request->proctoring_summary as $item) {
                if (is_array($item)) {
                    ProctoringLog::create([
                        'user_id'        => $userId,
                        'exam_result_id' => $result->id,
                        'violation_type' => $item['type'] ?? 'SNAPSHOT_CAPTURED',
                        'description'    => $item['description'] ?? ($item['desc'] ?? 'Pelanggaran proctoring'),
                        'question_index' => $item['question_index'] ?? null,
                        'timestamp_ms'   => $item['timestamp_ms'] ?? (int)(microtime(true) * 1000),
                    ]);
                }
            }
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Hasil ujian berhasil disimpan',
            'result_id' => $result->id,
            'score'     => $result->score,
        ], 201);
    }

    /**
     * Verify class / grade change verification code
     */
    public function verifyClassCode(Request $request): JsonResponse
    {
        $code = trim($request->input('code', ''));
        $validCode = \App\Models\AppSetting::get('class_change_code', 'unpkediri');

        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode penggantian kelas harus diisi',
            ], 422);
        }

        if (strcasecmp($code, $validCode) === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Kode verifikasi kelas valid!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode khusus salah! Perubahan kelas dibatalkan.',
        ], 403);
    }

    /**
     * Get class change code (for client sync if needed)
     */
    public function getClassChangeCode(): JsonResponse
    {
        return response()->json([
            'success'           => true,
            'class_change_code' => \App\Models\AppSetting::get('class_change_code', 'unpkediri'),
        ]);
    }

    /**
     * Get exam history for the current user
     */
    public function history(Request $request): JsonResponse
    {
        $userId = 1;
        try {
            if ($user = JWTAuth::parseToken()->authenticate()) {
                $userId = $user->id;
            }
        } catch (\Exception $e) {
            if ($request->filled('username')) {
                $found = User::where('username', $request->input('username'))->first();
                if ($found) {
                    $userId = $found->id;
                }
            }
        }

        $history = ExamResult::where('user_id', $userId)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id'                 => $r->id,
                    'subject_id'         => (string) $r->subject_id,
                    'subject_title'      => $r->subject->title ?? 'Unknown',
                    'grade'              => $r->grade,
                    'score'              => $r->score,
                    'correct_count'      => $r->correct_count,
                    'wrong_count'        => $r->wrong_count,
                    'unanswered_count'   => $r->unanswered_count,
                    'total_questions'    => $r->total_questions,
                    'time_spent_seconds' => $r->time_spent_seconds,
                    'taken_at'           => $r->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }
}
