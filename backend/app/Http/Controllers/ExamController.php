<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use App\Models\ProctoringLog;
use App\Models\Question;
use App\Models\Subject;
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
        $grade = $request->query('grade', 5);

        $subjects = Subject::where('grade', $grade)->get()->map(function ($s) {
            return [
                'id'               => $s->id,
                'title'            => $s->title,
                'grade'            => $s->grade,
                'duration_minutes' => $s->duration_minutes,
                'question_count'   => $s->questions()->count(),
            ];
        });

        return response()->json([
            'success'  => true,
            'grade'    => (int) $grade,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Get 20 questions for a subject (randomized)
     */
    public function questions(Request $request, int $subjectId): JsonResponse
    {
        $subject = Subject::findOrFail($subjectId);

        $questions = Question::where('subject_id', $subjectId)
            ->inRandomOrder()
            ->limit(20)
            ->get()
            ->map(function ($q) {
                return [
                    'id'                   => $q->id,
                    'prompt'               => $q->prompt,
                    'options'              => json_decode($q->options),
                    'correct_answer_index' => $q->correct_answer_index,
                ];
            });

        return response()->json([
            'success'  => true,
            'subject'  => [
                'id'               => $subject->id,
                'title'            => $subject->title,
                'duration_minutes' => $subject->duration_minutes,
            ],
            'questions' => $questions,
        ]);
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
            'user_answers'        => 'required|array',
            'proctoring_summary'  => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $result = ExamResult::create([
            'user_id'            => $user->id,
            'subject_id'         => $request->subject_id,
            'grade'              => $request->grade,
            'score'              => $request->score,
            'correct_count'      => $request->correct_count,
            'wrong_count'        => $request->wrong_count,
            'unanswered_count'   => $request->unanswered_count,
            'total_questions'    => $request->total_questions,
            'time_spent_seconds' => $request->time_spent_seconds,
            'user_answers'       => json_encode($request->user_answers),
            'proctoring_summary' => json_encode($request->proctoring_summary ?? []),
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Hasil ujian berhasil disimpan',
            'result_id' => $result->id,
            'score'     => $result->score,
        ], 201);
    }

    /**
     * Get exam history for the current user
     */
    public function history(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $history = ExamResult::where('user_id', $user->id)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id'                 => $r->id,
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
