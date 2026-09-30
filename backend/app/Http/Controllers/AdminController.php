<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ExamResult;
use App\Models\ProctoringLog;
use App\Models\ProctoringSnapshot;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Display the Admin Dashboard Web View
     */
    public function index(Request $request)
    {
        $grade = $request->query('grade');
        $selectedSubjectId = $request->query('subject_id');
        $tab = $request->query('tab', 'exams');

        $subjectsQuery = Subject::withCount('questions')->orderBy('grade')->orderBy('title');
        if ($grade && $grade !== 'all') {
            $subjectsQuery->where('grade', (int)$grade);
        }
        $subjects = $subjectsQuery->get();

        $selectedSubject = null;
        $questions = collect();
        if ($selectedSubjectId) {
            $selectedSubject = Subject::with('questions')->find($selectedSubjectId);
            if ($selectedSubject) {
                $questions = $selectedSubject->questions;
            }
        } elseif ($subjects->isNotEmpty()) {
            $selectedSubject = $subjects->first();
            $questions = $selectedSubject->questions;
        }

        $users = User::orderBy('is_admin', 'desc')->orderBy('name')->get();

        $stats = [
            'total_subjects'   => Subject::count(),
            'total_questions'  => Question::count(),
            'total_students'   => User::where('is_admin', false)->count(),
            'total_results'    => ExamResult::count(),
            'total_violations' => ProctoringLog::count(),
            'total_snapshots'  => ProctoringSnapshot::count(),
        ];

        // All exam results taken by users (sorted latest first)
        $recentResults = ExamResult::with(['user', 'subject'])->latest()->get();
        // Violation logs with user info
        $recentViolations = ProctoringLog::with(['user', 'examResult.subject'])->latest()->limit(50)->get();
        // Web camera snapshot captures
        $recentSnapshots = ProctoringSnapshot::with('user')->latest()->limit(24)->get();

        // System settings
        $classChangeCode = AppSetting::get('class_change_code', 'unpkediri');

        return view('admin.dashboard', compact(
            'subjects',
            'selectedSubject',
            'questions',
            'stats',
            'recentResults',
            'recentViolations',
            'recentSnapshots',
            'classChangeCode',
            'grade',
            'users',
            'tab'
        ));
    }

    /**
     * Store a new Exam Subject
     */
    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'grade'            => 'required|integer|min:1|max:6',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'max_violations'   => 'required|integer|min:1|max:20',
            'remedy_code'      => 'required|string|max:50',
            'description'      => 'nullable|string',
            'icon_name'        => 'nullable|string',
        ]);

        if (empty($validated['icon_name'])) {
            $validated['icon_name'] = 'school';
        }

        $subject = Subject::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'subject' => $subject], 201);
        }

        return redirect()->route('admin.dashboard', [
            'grade' => $subject->grade,
            'subject_id' => $subject->id
        ])->with('success', 'Ujian "' . $subject->title . '" Kelas ' . $subject->grade . ' berhasil ditambahkan!');
    }

    /**
     * Update an existing Exam Subject (termasuk ganti kelas, waktu, max pelanggaran, kode remedi)
     */
    public function updateSubject(Request $request, int $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'grade'            => 'required|integer|min:1|max:6',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'max_violations'   => 'required|integer|min:1|max:20',
            'remedy_code'      => 'required|string|max:50',
            'description'      => 'nullable|string',
            'icon_name'        => 'nullable|string',
        ]);

        $subject->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'subject' => $subject]);
        }

        return redirect()->route('admin.dashboard', [
            'grade' => $subject->grade,
            'subject_id' => $subject->id
        ])->with('success', 'Ujian "' . $subject->title . '" berhasil diperbarui!');
    }

    /**
     * Delete an Exam Subject
     */
    public function deleteSubject(Request $request, int $id)
    {
        $subject = Subject::findOrFail($id);
        $grade = $subject->grade;
        $subject->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Ujian berhasil dihapus']);
        }

        return redirect()->route('admin.dashboard', ['grade' => $grade])
            ->with('success', 'Ujian berhasil dihapus.');
    }

    /**
     * Store Question with optional images for prompt and options
     */
    public function storeQuestion(Request $request)
    {
        $validated = $request->validate([
            'subject_id'           => 'required|exists:subjects,id',
            'prompt'               => 'required|string',
            'correct_answer_index' => 'required|integer|min:0|max:3',
            'explanation'          => 'nullable|string',
            'option_0'             => 'required|string',
            'option_1'             => 'required|string',
            'option_2'             => 'required|string',
            'option_3'             => 'required|string',
            'question_image_file'  => 'nullable|image|max:5120',
            'question_image_url'   => 'nullable|string',
            'opt_image_file_0'     => 'nullable|image|max:5120',
            'opt_image_file_1'     => 'nullable|image|max:5120',
            'opt_image_file_2'     => 'nullable|image|max:5120',
            'opt_image_file_3'     => 'nullable|image|max:5120',
        ]);

        $options = [
            $request->input('option_0'),
            $request->input('option_1'),
            $request->input('option_2'),
            $request->input('option_3'),
        ];

        // Process question image
        $questionImage = null;
        if ($request->hasFile('question_image_file')) {
            $path = $request->file('question_image_file')->store('questions', 'public');
            $questionImage = Storage::url($path);
        } elseif (!empty($validated['question_image_url'])) {
            $questionImage = $validated['question_image_url'];
        }

        // Process option images
        $optionImages = [null, null, null, null];
        for ($i = 0; $i < 4; $i++) {
            if ($request->hasFile("opt_image_file_$i")) {
                $path = $request->file("opt_image_file_$i")->store('options', 'public');
                $optionImages[$i] = Storage::url($path);
            } elseif ($request->filled("opt_image_url_$i")) {
                $optionImages[$i] = $request->input("opt_image_url_$i");
            }
        }

        $question = Question::create([
            'subject_id'           => $validated['subject_id'],
            'prompt'               => $validated['prompt'],
            'question_image'       => $questionImage,
            'options'              => $options,
            'option_images'        => $optionImages,
            'correct_answer_index' => (int)$validated['correct_answer_index'],
            'explanation'          => $validated['explanation'] ?? '',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'question' => $question], 201);
        }

        $subject = Subject::find($validated['subject_id']);
        return redirect()->route('admin.dashboard', [
            'grade' => $subject?->grade,
            'subject_id' => $validated['subject_id']
        ])->with('success', 'Soal baru berhasil ditambahkan!');
    }

    /**
     * Update an existing Question
     */
    public function updateQuestion(Request $request, int $id)
    {
        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'prompt'               => 'required|string',
            'correct_answer_index' => 'required|integer|min:0|max:3',
            'explanation'          => 'nullable|string',
            'option_0'             => 'required|string',
            'option_1'             => 'required|string',
            'option_2'             => 'required|string',
            'option_3'             => 'required|string',
            'question_image_file'  => 'nullable|image|max:5120',
            'question_image_url'   => 'nullable|string',
        ]);

        $options = [
            $request->input('option_0'),
            $request->input('option_1'),
            $request->input('option_2'),
            $request->input('option_3'),
        ];

        $questionImage = $question->question_image;
        if ($request->hasFile('question_image_file')) {
            $path = $request->file('question_image_file')->store('questions', 'public');
            $questionImage = Storage::url($path);
        } elseif ($request->filled('question_image_url')) {
            $questionImage = $request->input('question_image_url');
        }

        $optionImages = $question->option_images ?: [null, null, null, null];
        for ($i = 0; $i < 4; $i++) {
            if ($request->hasFile("opt_image_file_$i")) {
                $path = $request->file("opt_image_file_$i")->store('options', 'public');
                $optionImages[$i] = Storage::url($path);
            } elseif ($request->filled("opt_image_url_$i")) {
                $optionImages[$i] = $request->input("opt_image_url_$i");
            }
        }

        $question->update([
            'prompt'               => $validated['prompt'],
            'question_image'       => $questionImage,
            'options'              => $options,
            'option_images'        => $optionImages,
            'correct_answer_index' => (int)$validated['correct_answer_index'],
            'explanation'          => $validated['explanation'] ?? '',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'question' => $question]);
        }

        $subject = $question->subject;
        return redirect()->route('admin.dashboard', [
            'grade' => $subject?->grade,
            'subject_id' => $question->subject_id
        ])->with('success', 'Soal berhasil diperbarui!');
    }

    /**
     * Delete a Question
     */
    public function deleteQuestion(Request $request, int $id)
    {
        $question = Question::findOrFail($id);
        $subjectId = $question->subject_id;
        $subject = $question->subject;
        $question->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Soal berhasil dihapus']);
        }

        return redirect()->route('admin.dashboard', [
            'grade' => $subject?->grade,
            'subject_id' => $subjectId
        ])->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Store a new User (Siswa / Guru)
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'username'    => 'required|string|max:100|unique:users,username',
            'nomor_induk' => 'required|string|max:50',
            'password'    => 'required|string|min:4',
            'grade'       => 'required|integer|min:1|max:6',
            'is_admin'    => 'nullable',
            'avatar'      => 'nullable|string',
        ]);

        $user = User::create([
            'name'           => $validated['name'],
            'username'       => strtolower(trim($validated['username'])),
            'nomor_induk'    => trim($validated['nomor_induk']),
            'password'       => Hash::make($validated['password']),
            'plain_password' => $validated['password'],
            'grade'          => (int)$validated['grade'],
            'is_admin'       => !empty($request->input('is_admin')),
            'avatar'         => $validated['avatar'] ?? 'av1',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'user' => $user], 201);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', 'Akun user "' . $user->name . '" berhasil ditambahkan!');
    }

    /**
     * Update an existing User
     */
    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'username'    => 'required|string|max:100|unique:users,username,' . $id,
            'nomor_induk' => 'nullable|string|max:50',
            'password'    => 'nullable|string|min:4',
            'grade'       => 'required|integer|min:1|max:6',
            'is_admin'    => 'nullable',
            'avatar'      => 'nullable|string',
        ]);

        $user->name = $validated['name'];
        $user->username = strtolower(trim($validated['username']));
        if ($request->filled('nomor_induk')) {
            $user->nomor_induk = trim($validated['nomor_induk']);
        }
        $user->grade = (int)$validated['grade'];
        $user->is_admin = !empty($request->input('is_admin'));
        if (!empty($validated['avatar'])) {
            $user->avatar = $validated['avatar'];
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->plain_password = $validated['password'];
        }
        $user->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'user' => $user]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', 'Akun user "' . $user->name . '" berhasil diperbarui!');
    }

    /**
     * Delete a User
     */
    public function deleteUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Akun berhasil dihapus']);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', 'Akun user "' . $name . '" berhasil dihapus.');
    }

    /**
     * Delete an exam result taken by a user (memungkinkan siswa mengambil ulang ujian)
     */
    public function deleteExamResult(Request $request, int $id)
    {
        $result = ExamResult::findOrFail($id);
        $userName = $result->user->name ?? 'Siswa';
        $subjectTitle = $result->subject->title ?? 'Ujian';

        // Delete associated proctoring logs & snapshots
        ProctoringLog::where('exam_result_id', $id)->delete();
        ProctoringSnapshot::where('exam_result_id', $id)->delete();

        $result->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Riwayat ujian {$subjectTitle} untuk {$userName} berhasil dihapus"]);
        }

        return redirect()->back()
            ->with('success', "Riwayat pengerjaan ujian '{$subjectTitle}' siswa '{$userName}' berhasil dihapus. Siswa kini dapat mengambil ulang ujian.");
    }

    /**
     * Update kode verifikasi penggantian kelas
     */
    public function updateClassChangeCode(Request $request)
    {
        $validated = $request->validate([
            'class_change_code' => 'required|string|max:100',
        ]);

        $newCode = trim($validated['class_change_code']);
        AppSetting::set('class_change_code', $newCode);

        if ($request->wantsJson()) {
            return response()->json([
                'success'           => true,
                'message'           => 'Kode penggantian kelas berhasil diperbarui',
                'class_change_code' => $newCode,
            ]);
        }

        return redirect()->back()
            ->with('success', "Kode penggantian kelas berhasil diubah menjadi: \"{$newCode}\"");
    }

    /**
     * Clear all proctoring logs
     */
    public function clearProctoringLogs(Request $request)
    {
        ProctoringLog::truncate();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Semua log proctoring berhasil dibersihkan']);
        }

        return redirect()->back()
            ->with('success', 'Semua riwayat catatan AI proctoring berhasil dibersihkan.');
    }
}
