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
use Illuminate\Support\Facades\Auth;
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
        $currentUser = Auth::user();
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

        $users = User::orderByRaw("CASE WHEN role = 'admin' THEN 1 WHEN role = 'teacher' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get();

        $stats = [
            'total_subjects'   => Subject::count(),
            'total_questions'  => Question::count(),
            'total_admins'     => User::where('role', 'admin')->count(),
            'total_teachers'   => User::where('role', 'teacher')->count(),
            'total_students'   => User::where(function($q) {
                $q->where('role', 'student')->orWhereNull('role');
            })->where('is_admin', false)->count(),
            'total_results'    => ExamResult::count(),
            'total_violations' => ProctoringLog::count(),
        ];

        // All exam results taken by users (sorted latest first)
        $recentResults = ExamResult::with(['user', 'subject'])->latest()->get();
        // Violation logs with user info
        $recentViolations = ProctoringLog::with(['user', 'examResult.subject'])->latest()->limit(50)->get();

        // System settings
        $classChangeCode = AppSetting::get('class_change_code', 'unpkediri');

        // Rekapitulasi Nilai Per Kelas (Kelas 1 - 6 SD)
        $recapGrade = $request->query('recap_grade');
        if ($recapGrade === 'all') {
            $recapGrade = null;
        }

        $allSubjectsByGrade = Subject::withCount('questions')->orderBy('grade')->orderBy('title')->get()->groupBy('grade');

        $classRecaps = [];
        for ($g = 1; $g <= 6; $g++) {
            $studentsCount = User::where('is_admin', false)->where('grade', $g)->count();
            $results = ExamResult::where('grade', $g)->get();
            $totalExams = $results->count();
            $avgScore = $totalExams > 0 ? round($results->avg('score'), 1) : 0;
            $maxScore = $totalExams > 0 ? $results->max('score') : 0;
            $minScore = $totalExams > 0 ? $results->min('score') : 0;
            $passedCount = $totalExams > 0 ? $results->where('score', '>=', 70)->count() : 0;
            $passRate = $totalExams > 0 ? round(($passedCount / $totalExams) * 100) : 0;
            $violationsCount = ProctoringLog::whereHas('user', function ($q) use ($g) {
                $q->where('grade', $g);
            })->count();
            $subjectsCount = $allSubjectsByGrade->get($g, collect())->count();

            $classRecaps[$g] = [
                'grade'            => $g,
                'students_count'   => $studentsCount,
                'total_exams'      => $totalExams,
                'avg_score'        => $avgScore,
                'max_score'        => $maxScore,
                'min_score'        => $minScore,
                'passed_count'     => $passedCount,
                'pass_rate'        => $passRate,
                'violations_count' => $violationsCount,
                'subjects_count'   => $subjectsCount,
            ];
        }

        // Rekap Siswa Detail (jika filter kelas dipilih atau semua)
        $detailedStudentsQuery = User::where('is_admin', false);
        if ($recapGrade) {
            $recapGradeInt = (int)$recapGrade;
            $detailedStudentsQuery->where(function ($q) use ($recapGradeInt) {
                $q->where('grade', $recapGradeInt)
                  ->orWhereHas('examResults', function ($er) use ($recapGradeInt) {
                      $er->where('grade', $recapGradeInt);
                  });
            });
        }

        $detailedStudents = $detailedStudentsQuery
            ->with(['examResults' => function ($q) use ($recapGrade) {
                if ($recapGrade) {
                    $q->where('grade', (int)$recapGrade);
                }
                $q->with('subject')->latest();
            }, 'proctoringLogs'])
            ->orderBy('grade')
            ->orderBy('name')
            ->get()
            ->map(function ($student) use ($recapGrade, $allSubjectsByGrade) {
                $results = $recapGrade 
                    ? $student->examResults->where('grade', (int)$recapGrade)
                    : $student->examResults;

                $totalExams = $results->count();
                $avgScore = $totalExams > 0 ? round($results->avg('score'), 1) : 0;
                $maxScore = $totalExams > 0 ? $results->max('score') : 0;
                $minScore = $totalExams > 0 ? $results->min('score') : 0;
                $totalCorrect = $results->sum('correct_count');
                $totalWrong = $results->sum('wrong_count');
                $totalUnanswered = $results->sum('unanswered_count');
                $totalQuestionsWorked = $totalCorrect + $totalWrong;
                $violations = $student->proctoringLogs->count();

                // Target kelas untuk evaluasi mata pelajaran
                $targetGrade = $recapGrade ? (int)$recapGrade : (int)($student->grade ?? 1);
                $availableSubjects = $allSubjectsByGrade->get($targetGrade, collect());

                // ID ujian yang sudah diselesaikan siswa
                $takenSubjectIds = $results->pluck('subject_id')->filter()->unique()->toArray();

                // Daftar mata pelajaran yang BELUM dikerjakan oleh siswa
                $uncompletedSubjects = $availableSubjects->reject(function ($s) use ($takenSubjectIds) {
                    return in_array($s->id, $takenSubjectIds);
                })->values();

                $totalAvailableExams = $availableSubjects->count();
                $completedExamsCount = count($takenSubjectIds);
                $uncompletedCount = $uncompletedSubjects->count();
                $isAllCompleted = $totalAvailableExams > 0 && $uncompletedCount === 0;

                // Pengolahan Predikat & KKM
                $predicate = 'D';
                $predicateLabel = 'Perlu Remedial';
                if ($totalExams === 0) {
                    $predicate = '-';
                    $predicateLabel = 'Belum Ujian';
                } elseif ($avgScore >= 85) {
                    $predicate = 'A';
                    $predicateLabel = 'Sangat Baik (Tuntas)';
                } elseif ($avgScore >= 70) {
                    $predicate = 'B';
                    $predicateLabel = 'Baik (Tuntas KKM)';
                } elseif ($avgScore >= 55) {
                    $predicate = 'C';
                    $predicateLabel = 'Cukup (Perlu Bimbingan)';
                }

                return [
                    'id'                     => $student->id,
                    'name'                   => $student->name,
                    'username'               => $student->username,
                    'nomor_induk'            => $student->nomor_induk,
                    'avatar'                 => $student->avatar,
                    'grade'                  => $student->grade,
                    'total_exams'            => $totalExams,
                    'avg_score'              => $avgScore,
                    'max_score'              => $maxScore,
                    'min_score'              => $minScore,
                    'total_correct'          => $totalCorrect,
                    'total_wrong'            => $totalWrong,
                    'total_unanswered'       => $totalUnanswered,
                    'total_questions_worked' => $totalQuestionsWorked,
                    'violations_count'       => $violations,
                    'exam_results'           => $results->values(),
                    'uncompleted_subjects'   => $uncompletedSubjects,
                    'total_available_exams'  => $totalAvailableExams,
                    'completed_exams_count'  => $completedExamsCount,
                    'uncompleted_count'      => $uncompletedCount,
                    'is_all_completed'       => $isAllCompleted,
                    'predicate'              => $predicate,
                    'predicate_label'        => $predicateLabel,
                ];
            });

        return view('admin.dashboard', compact(
            'subjects',
            'selectedSubject',
            'questions',
            'stats',
            'recentResults',
            'recentViolations',
            'classChangeCode',
            'grade',
            'users',
            'tab',
            'classRecaps',
            'recapGrade',
            'detailedStudents',
            'currentUser'
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
            'max_questions'    => 'nullable|integer|min:1|max:200',
            'max_violations'   => 'required|integer|min:1|max:20',
            'remedy_code'      => 'required|string|max:50',
            'description'      => 'nullable|string',
            'icon_name'        => 'nullable|string',
            'icon_file'        => 'nullable|image|max:5120',
        ]);

        // Default max_questions = 20 jika tidak diisi
        if (empty($validated['max_questions'])) {
            $validated['max_questions'] = 20;
        }

        if ($request->hasFile('icon_file')) {
            $path = $request->file('icon_file')->store('subjects', 'public');
            $validated['icon_name'] = Storage::url($path);
        } elseif (empty($validated['icon_name'])) {
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
            'max_questions'    => 'nullable|integer|min:1|max:200',
            'max_violations'   => 'required|integer|min:1|max:20',
            'remedy_code'      => 'required|string|max:50',
            'description'      => 'nullable|string',
            'icon_name'        => 'nullable|string',
            'icon_file'        => 'nullable|image|max:5120',
        ]);

        if (empty($validated['max_questions'])) {
            $validated['max_questions'] = $subject->max_questions ?? 20;
        }

        if ($request->hasFile('icon_file')) {
            // Delete old custom file if it exists in public storage
            if ($subject->icon_name && str_starts_with($subject->icon_name, '/storage/subjects/')) {
                $oldPath = str_replace('/storage/', '', $subject->icon_name);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('icon_file')->store('subjects', 'public');
            $validated['icon_name'] = Storage::url($path);
        }

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
     * Helper to parse dynamic question options and their optional images
     */
    private function parseOptionsAndImages(Request $request, ?Question $existingQuestion = null): array
    {
        $options = [];
        $optionImages = [];

        if ($request->has('options') && is_array($request->input('options'))) {
            foreach ($request->input('options') as $idx => $optText) {
                if (trim((string)$optText) !== '') {
                    $options[] = trim((string)$optText);
                }
            }
        } else {
            $i = 0;
            while ($request->has("option_$i")) {
                $val = trim((string)$request->input("option_$i"));
                if ($val !== '') {
                    $options[] = $val;
                }
                $i++;
            }
        }

        if (count($options) < 2) {
            $options = ['Pilihan A', 'Pilihan B'];
        }

        $existingImages = $existingQuestion?->option_images ?: [];

        foreach ($options as $idx => $opt) {
            $img = $existingImages[$idx] ?? null;

            if ($request->hasFile("opt_image_file_$idx")) {
                $path = $request->file("opt_image_file_$idx")->store('options', 'public');
                $img = Storage::url($path);
            } elseif ($request->filled("opt_image_url_$idx")) {
                $img = $request->input("opt_image_url_$idx");
            }

            $optionImages[] = $img;
        }

        return [$options, $optionImages];
    }

    /**
     * Store Question with dynamic options and optional images
     */
    public function storeQuestion(Request $request)
    {
        $validated = $request->validate([
            'subject_id'           => 'required|exists:subjects,id',
            'prompt'               => 'required|string',
            'correct_answer_index' => 'required|integer|min:0',
            'explanation'          => 'nullable|string',
            'question_image_file'  => 'nullable|image|max:5120',
            'question_image_url'   => 'nullable|string',
        ]);

        [$options, $optionImages] = $this->parseOptionsAndImages($request);

        $correctIndex = (int)$validated['correct_answer_index'];
        if ($correctIndex >= count($options)) {
            $correctIndex = 0;
        }

        // Process question image
        $questionImage = null;
        if ($request->hasFile('question_image_file')) {
            $path = $request->file('question_image_file')->store('questions', 'public');
            $questionImage = Storage::url($path);
        } elseif (!empty($validated['question_image_url'])) {
            $questionImage = $validated['question_image_url'];
        }

        $question = Question::create([
            'subject_id'           => $validated['subject_id'],
            'prompt'               => $validated['prompt'],
            'question_image'       => $questionImage,
            'options'              => $options,
            'option_images'        => $optionImages,
            'correct_answer_index' => $correctIndex,
            'explanation'          => $validated['explanation'] ?? '',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'question' => $question], 201);
        }

        $subject = Subject::find($validated['subject_id']);
        return redirect()->route('admin.dashboard', [
            'grade' => $subject?->grade,
            'subject_id' => $validated['subject_id']
        ])->with('success', 'Soal baru berhasil ditambahkan (' . count($options) . ' pilihan jawaban)!');
    }

    /**
     * Update an existing Question
     */
    public function updateQuestion(Request $request, int $id)
    {
        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'prompt'               => 'required|string',
            'correct_answer_index' => 'required|integer|min:0',
            'explanation'          => 'nullable|string',
            'question_image_file'  => 'nullable|image|max:5120',
            'question_image_url'   => 'nullable|string',
        ]);

        [$options, $optionImages] = $this->parseOptionsAndImages($request, $question);

        $correctIndex = (int)$validated['correct_answer_index'];
        if ($correctIndex >= count($options)) {
            $correctIndex = 0;
        }

        $questionImage = $question->question_image;
        if ($request->hasFile('question_image_file')) {
            $path = $request->file('question_image_file')->store('questions', 'public');
            $questionImage = Storage::url($path);
        } elseif ($request->filled('question_image_url')) {
            $questionImage = $request->input('question_image_url');
        }

        $question->update([
            'prompt'               => $validated['prompt'],
            'question_image'       => $questionImage,
            'options'              => $options,
            'option_images'        => $optionImages,
            'correct_answer_index' => $correctIndex,
            'explanation'          => $validated['explanation'] ?? '',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'question' => $question]);
        }

        $subject = $question->subject;
        return redirect()->route('admin.dashboard', [
            'grade' => $subject?->grade,
            'subject_id' => $question->subject_id
        ])->with('success', 'Soal berhasil diperbarui (' . count($options) . ' pilihan jawaban)!');
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
     * Store a new User (Siswa / Guru / Admin)
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'username'    => 'required|string|max:100|unique:users,username',
            'nomor_induk' => 'required|string|max:50',
            'password'    => 'required|string|min:4',
            'grade'       => 'required|integer|min:1|max:6',
            'role'        => 'nullable|string|in:admin,teacher,student',
            'avatar'      => 'nullable|string',
        ]);

        $role    = $request->input('role', 'student');
        $isAdmin = in_array($role, ['admin', 'teacher']);

        $user = User::create([
            'name'           => $validated['name'],
            'username'       => strtolower(trim($validated['username'])),
            'nomor_induk'    => trim($validated['nomor_induk']),
            'password'       => Hash::make($validated['password']),
            'plain_password' => $validated['password'],
            'grade'          => (int)$validated['grade'],
            'role'           => $role,
            'is_admin'       => $isAdmin,
            'avatar'         => $validated['avatar'] ?? 'av1',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'user' => $user], 201);
        }

        $roleLabel = match($role) { 'admin' => 'Administrator', 'teacher' => 'Guru/Pengawas', default => 'Siswa' };
        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', 'Akun "' . $user->name . '" (' . $roleLabel . ') berhasil ditambahkan!');
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
            'role'        => 'nullable|string|in:admin,teacher,student',
            'avatar'      => 'nullable|string',
        ]);

        $role    = $request->input('role', $user->role ?: 'student');
        $isAdmin = in_array($role, ['admin', 'teacher']);

        $user->name     = $validated['name'];
        $user->username = strtolower(trim($validated['username']));
        if ($request->filled('nomor_induk')) {
            $user->nomor_induk = trim($validated['nomor_induk']);
        }
        $user->grade    = (int)$validated['grade'];
        $user->role     = $role;
        $user->is_admin = $isAdmin;
        if (!empty($validated['avatar'])) {
            $user->avatar = $validated['avatar'];
        }
        if (!empty($validated['password'])) {
            $user->password      = Hash::make($validated['password']);
            $user->plain_password = $validated['password'];
        }
        $user->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'user' => $user]);
        }

        $roleLabel = match($role) { 'admin' => 'Administrator', 'teacher' => 'Guru/Pengawas', default => 'Siswa' };
        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', 'Akun "' . $user->name . '" (' . $roleLabel . ') berhasil diperbarui!');
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
