<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\QuestionBanks\MatematikaQuestions;
use Database\Seeders\QuestionBanks\IpaQuestions;
use Database\Seeders\QuestionBanks\BahasaIndonesiaQuestions;
use Database\Seeders\QuestionBanks\PknQuestions;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────────
        // Initial Accounts (password: unpkediri)
        // ──────────────────────────────────────────────
        $accounts = [
            ['name' => 'Super Admin',       'username' => 'admin',    'nomor_induk' => '198507152010011001', 'grade' => 5, 'avatar' => 'champion',  'role' => 'admin',   'is_admin' => true],
            ['name' => 'Guru & Pengawas',   'username' => 'guru',     'nomor_induk' => '198803152012012002', 'grade' => 5, 'avatar' => 'champion',  'role' => 'teacher', 'is_admin' => true],
            ['name' => 'Zam Zam',           'username' => 'zamzam',   'nomor_induk' => '2024001',            'grade' => 5, 'avatar' => 'rabbit',    'role' => 'teacher', 'is_admin' => true],
            ['name' => 'Yusuf',             'username' => 'yusuf',    'nomor_induk' => '2024002',            'grade' => 5, 'avatar' => 'bear',      'role' => 'student', 'is_admin' => false],
            ['name' => 'Yehosyua',          'username' => 'yehosyua', 'nomor_induk' => '2024003',            'grade' => 5, 'avatar' => 'robot',     'role' => 'student', 'is_admin' => false],
            ['name' => 'Cantika',           'username' => 'cantika',  'nomor_induk' => '2024004',            'grade' => 5, 'avatar' => 'astronaut', 'role' => 'student', 'is_admin' => false],
        ];

        foreach ($accounts as $s) {
            $user = User::where('username', $s['username'])->first();
            if ($user) {
                $user->role = $s['role'];
                $user->is_admin = $s['is_admin'];
                if (empty($user->nomor_induk)) {
                    $user->nomor_induk = $s['nomor_induk'];
                }
                $user->plain_password = 'unpkediri';
                $user->save();
            } else {
                User::create([
                    'name'           => $s['name'],
                    'username'       => $s['username'],
                    'nomor_induk'    => $s['nomor_induk'],
                    'password'       => Hash::make('unpkediri'),
                    'plain_password' => 'unpkediri',
                    'grade'          => $s['grade'],
                    'avatar'         => $s['avatar'],
                    'role'           => $s['role'],
                    'is_admin'       => $s['is_admin'],
                ]);
            }
        }

        // ──────────────────────────────────────────────
        // Subjects per Grade (Kelas 1-6, 4 Mata Pelajaran)
        // ──────────────────────────────────────────────
        $subjectDefs = [
            ['title' => 'Matematika',        'duration_minutes' => 45, 'icon_name' => 'calculate', 'description' => 'Ujian Matematika Komprehensif'],
            ['title' => 'IPA',               'duration_minutes' => 45, 'icon_name' => 'science',   'description' => 'Ilmu Pengetahuan Alam dan Lingkungan'],
            ['title' => 'Bahasa Indonesia',  'duration_minutes' => 45, 'icon_name' => 'menu_book', 'description' => 'Tata Bahasa & Pemahaman Teks'],
            ['title' => 'PKn',               'duration_minutes' => 30, 'icon_name' => 'flag',      'description' => 'Pendidikan Pancasila dan Kewarganegaraan'],
        ];

        for ($grade = 1; $grade <= 6; $grade++) {
            foreach ($subjectDefs as $sd) {
                $subject = Subject::firstOrCreate([
                    'title' => $sd['title'],
                    'grade' => $grade,
                ], [
                    'duration_minutes' => $sd['duration_minutes'],
                    'max_violations'   => 3,
                    'remedy_code'      => 'REMEDI' . $grade,
                    'icon_name'        => $sd['icon_name'],
                    'description'      => $sd['description'],
                ]);

                // Seed 40 questions per subject per grade
                if ($subject->questions()->count() < 40) {
                    $this->seedQuestions($subject, $grade);
                }
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Question Data per Subject and Grade (40 questions each)
    // ──────────────────────────────────────────────────────────────────────────
    public function seedQuestions(Subject $subject, int $grade): void
    {
        $questions = $this->getQuestionsForSubjectAndGrade($subject->title, $grade);
        $existingPrompts = $subject->questions()->pluck('prompt')->map(fn($p) => trim($p))->toArray();

        foreach ($questions as $q) {
            if (in_array(trim($q['prompt']), $existingPrompts)) {
                continue;
            }

            if ($subject->questions()->count() >= 40) {
                break;
            }

            Question::create([
                'subject_id'           => $subject->id,
                'prompt'               => $q['prompt'],
                'options'              => $q['options'],
                'correct_answer_index' => $q['correct'],
                'explanation'          => $q['explanation'] ?? ('Pembahasan: Jawaban yang tepat adalah ' . $q['options'][$q['correct']]),
            ]);
        }
    }

    public function getQuestionsForSubjectAndGrade(string $title, int $grade): array
    {
        $cleanTitle = trim(preg_replace('/\s+\d+$/', '', $title));

        return match ($cleanTitle) {
            'Matematika'       => MatematikaQuestions::get($grade),
            'IPA'              => IpaQuestions::get($grade),
            'Bahasa Indonesia' => BahasaIndonesiaQuestions::get($grade),
            'PKn'              => PknQuestions::get($grade),
            default            => MatematikaQuestions::get($grade),
        };
    }
}
