<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────────
        // 4 Initial Student Accounts (password: unpkediri)
        // ──────────────────────────────────────────────
        $students = [
            ['name' => 'Zam Zam',   'username' => 'zamzam',   'grade' => 5, 'avatar' => 'rabbit'],
            ['name' => 'Yusuf',     'username' => 'yusuf',     'grade' => 5, 'avatar' => 'bear'],
            ['name' => 'Yehosyua',  'username' => 'yehosyua',  'grade' => 5, 'avatar' => 'robot'],
            ['name' => 'Cantika',   'username' => 'cantika',   'grade' => 5, 'avatar' => 'astronaut'],
        ];

        foreach ($students as $s) {
            User::firstOrCreate(['username' => $s['username']], [
                'name'     => $s['name'],
                'password' => Hash::make('unpkediri'),
                'grade'    => $s['grade'],
                'avatar'   => $s['avatar'],
            ]);
        }

        // ──────────────────────────────────────────────
        // Subjects per Grade (Kelas 1-6, 4 Mata Pelajaran)
        // ──────────────────────────────────────────────
        $subjectDefs = [
            ['title' => 'Matematika',        'duration_minutes' => 45],
            ['title' => 'IPA',               'duration_minutes' => 45],
            ['title' => 'Bahasa Indonesia',  'duration_minutes' => 45],
            ['title' => 'PKn',               'duration_minutes' => 30],
        ];

        for ($grade = 1; $grade <= 6; $grade++) {
            foreach ($subjectDefs as $sd) {
                $subject = Subject::firstOrCreate([
                    'title' => $sd['title'],
                    'grade' => $grade,
                ], [
                    'duration_minutes' => $sd['duration_minutes'],
                ]);

                // Seed 20 questions per subject per grade
                if ($subject->questions()->count() < 20) {
                    $this->seedQuestions($subject, $grade);
                }
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Question Data per Subject and Grade
    // ──────────────────────────────────────────────────────────────────────────
    private function seedQuestions(Subject $subject, int $grade): void
    {
        $questions = $this->getQuestionsForSubjectAndGrade($subject->title, $grade);

        foreach ($questions as $q) {
            Question::create([
                'subject_id'           => $subject->id,
                'prompt'               => $q['prompt'],
                'options'              => json_encode($q['options']),
                'correct_answer_index' => $q['correct'],
            ]);
        }
    }

    private function getQuestionsForSubjectAndGrade(string $title, int $grade): array
    {
        // ── MATEMATIKA ─────────────────────────────────────────────────────────
        if ($title === 'Matematika') {
            if ($grade === 1) return [
                ['prompt'=>'1 + 1 = ?', 'options'=>['1','2','3','4'], 'correct'=>1],
                ['prompt'=>'2 + 3 = ?', 'options'=>['4','5','6','7'], 'correct'=>1],
                ['prompt'=>'5 - 2 = ?', 'options'=>['2','3','4','1'], 'correct'=>1],
                ['prompt'=>'4 + 4 = ?', 'options'=>['6','7','8','9'], 'correct'=>2],
                ['prompt'=>'10 - 5 = ?', 'options'=>['3','4','5','6'], 'correct'=>2],
                ['prompt'=>'3 + 6 = ?', 'options'=>['7','8','9','10'], 'correct'=>2],
                ['prompt'=>'7 - 3 = ?', 'options'=>['3','4','5','2'], 'correct'=>1],
                ['prompt'=>'2 × 3 = ?', 'options'=>['4','5','6','7'], 'correct'=>2],
                ['prompt'=>'8 - 4 = ?', 'options'=>['3','4','5','6'], 'correct'=>1],
                ['prompt'=>'5 + 5 = ?', 'options'=>['8','9','10','11'], 'correct'=>2],
                ['prompt'=>'6 + 2 = ?', 'options'=>['7','8','9','6'], 'correct'=>1],
                ['prompt'=>'9 - 6 = ?', 'options'=>['2','3','4','5'], 'correct'=>1],
                ['prompt'=>'4 + 3 = ?', 'options'=>['6','7','8','9'], 'correct'=>1],
                ['prompt'=>'10 - 7 = ?', 'options'=>['2','3','4','5'], 'correct'=>1],
                ['prompt'=>'1 × 5 = ?', 'options'=>['4','5','6','3'], 'correct'=>1],
                ['prompt'=>'6 + 3 = ?', 'options'=>['7','8','9','10'], 'correct'=>2],
                ['prompt'=>'7 - 2 = ?', 'options'=>['4','5','6','3'], 'correct'=>1],
                ['prompt'=>'3 × 2 = ?', 'options'=>['4','5','6','7'], 'correct'=>2],
                ['prompt'=>'9 + 1 = ?', 'options'=>['9','10','11','8'], 'correct'=>1],
                ['prompt'=>'10 ÷ 2 = ?', 'options'=>['4','5','6','3'], 'correct'=>1],
            ];
            if ($grade === 2) return [
                ['prompt'=>'12 + 8 = ?', 'options'=>['18','19','20','21'], 'correct'=>2],
                ['prompt'=>'25 - 10 = ?', 'options'=>['13','14','15','16'], 'correct'=>2],
                ['prompt'=>'3 × 4 = ?', 'options'=>['10','11','12','13'], 'correct'=>2],
                ['prompt'=>'20 ÷ 4 = ?', 'options'=>['4','5','6','3'], 'correct'=>1],
                ['prompt'=>'15 + 15 = ?', 'options'=>['28','29','30','31'], 'correct'=>2],
                ['prompt'=>'40 - 15 = ?', 'options'=>['23','24','25','26'], 'correct'=>2],
                ['prompt'=>'6 × 3 = ?', 'options'=>['16','17','18','19'], 'correct'=>2],
                ['prompt'=>'30 ÷ 5 = ?', 'options'=>['5','6','7','4'], 'correct'=>1],
                ['prompt'=>'18 + 7 = ?', 'options'=>['23','24','25','26'], 'correct'=>2],
                ['prompt'=>'50 - 20 = ?', 'options'=>['28','29','30','31'], 'correct'=>2],
                ['prompt'=>'4 × 5 = ?', 'options'=>['18','19','20','21'], 'correct'=>2],
                ['prompt'=>'24 ÷ 4 = ?', 'options'=>['5','6','7','4'], 'correct'=>1],
                ['prompt'=>'33 + 17 = ?', 'options'=>['48','49','50','51'], 'correct'=>2],
                ['prompt'=>'60 - 25 = ?', 'options'=>['33','34','35','36'], 'correct'=>2],
                ['prompt'=>'7 × 3 = ?', 'options'=>['19','20','21','22'], 'correct'=>2],
                ['prompt'=>'36 ÷ 6 = ?', 'options'=>['5','6','7','4'], 'correct'=>1],
                ['prompt'=>'45 + 5 = ?', 'options'=>['48','49','50','51'], 'correct'=>2],
                ['prompt'=>'80 - 35 = ?', 'options'=>['43','44','45','46'], 'correct'=>2],
                ['prompt'=>'8 × 4 = ?', 'options'=>['30','31','32','33'], 'correct'=>2],
                ['prompt'=>'42 ÷ 7 = ?', 'options'=>['5','6','7','4'], 'correct'=>1],
            ];
            if ($grade === 3) return [
                ['prompt'=>'125 + 75 = ?', 'options'=>['195','200','205','210'], 'correct'=>1],
                ['prompt'=>'300 - 145 = ?', 'options'=>['153','154','155','156'], 'correct'=>2],
                ['prompt'=>'12 × 9 = ?', 'options'=>['106','107','108','109'], 'correct'=>2],
                ['prompt'=>'144 ÷ 12 = ?', 'options'=>['11','12','13','10'], 'correct'=>1],
                ['prompt'=>'250 + 150 = ?', 'options'=>['398','399','400','401'], 'correct'=>2],
                ['prompt'=>'500 - 275 = ?', 'options'=>['223','224','225','226'], 'correct'=>2],
                ['prompt'=>'15 × 8 = ?', 'options'=>['118','119','120','121'], 'correct'=>2],
                ['prompt'=>'180 ÷ 15 = ?', 'options'=>['11','12','13','10'], 'correct'=>1],
                ['prompt'=>'375 + 125 = ?', 'options'=>['498','499','500','501'], 'correct'=>2],
                ['prompt'=>'700 - 350 = ?', 'options'=>['348','349','350','351'], 'correct'=>2],
                ['prompt'=>'25 × 4 = ?', 'options'=>['98','99','100','101'], 'correct'=>2],
                ['prompt'=>'240 ÷ 8 = ?', 'options'=>['28','29','30','31'], 'correct'=>2],
                ['prompt'=>'450 + 275 = ?', 'options'=>['723','724','725','726'], 'correct'=>2],
                ['prompt'=>'850 - 425 = ?', 'options'=>['423','424','425','426'], 'correct'=>2],
                ['prompt'=>'30 × 7 = ?', 'options'=>['208','209','210','211'], 'correct'=>2],
                ['prompt'=>'360 ÷ 9 = ?', 'options'=>['38','39','40','41'], 'correct'=>2],
                ['prompt'=>'625 + 375 = ?', 'options'=>['998','999','1000','1001'], 'correct'=>2],
                ['prompt'=>'900 - 550 = ?', 'options'=>['348','349','350','351'], 'correct'=>2],
                ['prompt'=>'45 × 5 = ?', 'options'=>['223','224','225','226'], 'correct'=>2],
                ['prompt'=>'480 ÷ 16 = ?', 'options'=>['28','29','30','31'], 'correct'=>2],
            ];
            if ($grade === 4) return [
                ['prompt'=>'1.250 + 750 = ?', 'options'=>['1.998','1.999','2.000','2.001'], 'correct'=>2],
                ['prompt'=>'3.500 - 1.250 = ?', 'options'=>['2.248','2.249','2.250','2.251'], 'correct'=>2],
                ['prompt'=>'125 × 8 = ?', 'options'=>['998','999','1.000','1.001'], 'correct'=>2],
                ['prompt'=>'2.400 ÷ 24 = ?', 'options'=>['98','99','100','101'], 'correct'=>2],
                ['prompt'=>'Keliling persegi sisi 8 cm = ?', 'options'=>['28 cm','30 cm','32 cm','34 cm'], 'correct'=>2],
                ['prompt'=>'Luas persegi panjang 10 × 6 cm = ?', 'options'=>['58 cm²','60 cm²','62 cm²','64 cm²'], 'correct'=>1],
                ['prompt'=>'4.750 + 1.250 = ?', 'options'=>['5.998','5.999','6.000','6.001'], 'correct'=>2],
                ['prompt'=>'7.800 - 3.400 = ?', 'options'=>['4.398','4.399','4.400','4.401'], 'correct'=>2],
                ['prompt'=>'250 × 16 = ?', 'options'=>['3.998','3.999','4.000','4.001'], 'correct'=>2],
                ['prompt'=>'9.600 ÷ 32 = ?', 'options'=>['298','299','300','301'], 'correct'=>2],
                ['prompt'=>'FPB dari 24 dan 36 = ?', 'options'=>['10','11','12','13'], 'correct'=>2],
                ['prompt'=>'KPK dari 4 dan 6 = ?', 'options'=>['10','11','12','13'], 'correct'=>2],
                ['prompt'=>'¼ + ½ = ?', 'options'=>['2/4','3/4','4/4','1/4'], 'correct'=>1],
                ['prompt'=>'0,5 + 0,25 = ?', 'options'=>['0,70','0,75','0,80','0,85'], 'correct'=>1],
                ['prompt'=>'Keliling lingkaran jika r=7 (π≈22/7) = ?', 'options'=>['42 cm','44 cm','46 cm','48 cm'], 'correct'=>1],
                ['prompt'=>'12.000 ÷ 40 = ?', 'options'=>['298','299','300','301'], 'correct'=>2],
                ['prompt'=>'3/4 - 1/4 = ?', 'options'=>['1/4','2/4','3/4','4/4'], 'correct'=>1],
                ['prompt'=>'5.600 ÷ 14 = ?', 'options'=>['398','399','400','401'], 'correct'=>2],
                ['prompt'=>'Luas segitiga alas 10 dan tinggi 8 = ?', 'options'=>['38 cm²','40 cm²','42 cm²','44 cm²'], 'correct'=>1],
                ['prompt'=>'2/3 + 1/3 = ?', 'options'=>['2/3','3/3','4/3','1/3'], 'correct'=>1],
            ];
            if ($grade === 5) return [
                ['prompt'=>'15.000 + 7.500 = ?', 'options'=>['22.498','22.499','22.500','22.501'], 'correct'=>2],
                ['prompt'=>'Persentase 75 dari 300 = ?', 'options'=>['23%','24%','25%','26%'], 'correct'=>2],
                ['prompt'=>'3² + 4² = ?', 'options'=>['23','24','25','26'], 'correct'=>2],
                ['prompt'=>'√144 = ?', 'options'=>['11','12','13','10'], 'correct'=>1],
                ['prompt'=>'Volume kubus sisi 5 cm = ?', 'options'=>['123 cm³','124 cm³','125 cm³','126 cm³'], 'correct'=>2],
                ['prompt'=>'Luas trapesium alas 12, atas 8, tinggi 5 = ?', 'options'=>['48 cm²','50 cm²','52 cm²','54 cm²'], 'correct'=>1],
                ['prompt'=>'FPB dari 60 dan 84 = ?', 'options'=>['10','11','12','13'], 'correct'=>2],
                ['prompt'=>'KPK dari 12 dan 18 = ?', 'options'=>['34','35','36','37'], 'correct'=>2],
                ['prompt'=>'2/5 + 3/10 = ?', 'options'=>['6/10','7/10','8/10','9/10'], 'correct'=>1],
                ['prompt'=>'0,75 × 40 = ?', 'options'=>['28','29','30','31'], 'correct'=>2],
                ['prompt'=>'Sudut lancip adalah sudut yang besarnya...', 'options'=>['> 90°','= 90°','< 90°','= 180°'], 'correct'=>2],
                ['prompt'=>'Jika kecepatan 60 km/jam, jarak 180 km, waktu = ?', 'options'=>['2 jam','3 jam','4 jam','5 jam'], 'correct'=>1],
                ['prompt'=>'5³ = ?', 'options'=>['123','124','125','126'], 'correct'=>2],
                ['prompt'=>'√225 = ?', 'options'=>['13','14','15','16'], 'correct'=>2],
                ['prompt'=>'Luas lingkaran r=7 cm (π≈22/7) = ?', 'options'=>['152 cm²','153 cm²','154 cm²','155 cm²'], 'correct'=>2],
                ['prompt'=>'Bilangan prima antara 20 dan 30 = ?', 'options'=>['21 dan 27','23 dan 29','22 dan 28','24 dan 26'], 'correct'=>1],
                ['prompt'=>'40% dari 250 = ?', 'options'=>['98','99','100','101'], 'correct'=>2],
                ['prompt'=>'Selisih 3/4 dan 1/4 = ?', 'options'=>['1/4','2/4','3/4','4/4'], 'correct'=>1],
                ['prompt'=>'Keliling persegi panjang 15 × 10 = ?', 'options'=>['48 cm','49 cm','50 cm','51 cm'], 'correct'=>2],
                ['prompt'=>'75% dari 200 = ?', 'options'=>['148','149','150','151'], 'correct'=>2],
            ];
            // Grade 6
            return [
                ['prompt'=>'12.500 × 4 = ?', 'options'=>['49.998','49.999','50.000','50.001'], 'correct'=>2],
                ['prompt'=>'Persentase kenaikan dari 80 ke 100 = ?', 'options'=>['23%','24%','25%','26%'], 'correct'=>2],
                ['prompt'=>'Volume balok 10 × 8 × 6 cm = ?', 'options'=>['478 cm³','479 cm³','480 cm³','481 cm³'], 'correct'=>2],
                ['prompt'=>'7² - 5² = ?', 'options'=>['22','23','24','25'], 'correct'=>2],
                ['prompt'=>'√256 = ?', 'options'=>['14','15','16','17'], 'correct'=>2],
                ['prompt'=>'Luas tabung tanpa tutup, r=7 t=10 (π≈22/7) = ?', 'options'=>['438 cm²','440 cm²','442 cm²','444 cm²'], 'correct'=>1],
                ['prompt'=>'Rata-rata dari 60, 70, 80, 90 = ?', 'options'=>['73','74','75','76'], 'correct'=>2],
                ['prompt'=>'Jika skala peta 1:50.000, jarak peta 4 cm = ?', 'options'=>['1,9 km','2,0 km','2,1 km','2,2 km'], 'correct'=>1],
                ['prompt'=>'FPB dari 72 dan 96 = ?', 'options'=>['22','23','24','25'], 'correct'=>2],
                ['prompt'=>'KPK dari 15 dan 25 = ?', 'options'=>['73','74','75','76'], 'correct'=>2],
                ['prompt'=>'5/8 + 1/4 = ?', 'options'=>['6/8','7/8','8/8','5/8'], 'correct'=>1],
                ['prompt'=>'Modus dari data: 3,3,4,5,5,5,6 = ?', 'options'=>['3','4','5','6'], 'correct'=>2],
                ['prompt'=>'Median dari: 2,4,6,8,10 = ?', 'options'=>['4','5','6','7'], 'correct'=>2],
                ['prompt'=>'30% dari 1500 = ?', 'options'=>['448','449','450','451'], 'correct'=>2],
                ['prompt'=>'Diskon 20% harga Rp 150.000 = ?', 'options'=>['Rp 118.000','Rp 119.000','Rp 120.000','Rp 121.000'], 'correct'=>2],
                ['prompt'=>'Volume kerucut r=7 t=12 (π≈22/7) = ?', 'options'=>['614 cm³','615 cm³','616 cm³','617 cm³'], 'correct'=>2],
                ['prompt'=>'9² + 4² = ?', 'options'=>['94','95','96','97'], 'correct'=>2],
                ['prompt'=>'Luas trapesium alas 20, atas 12, tinggi 8 = ?', 'options'=>['126 cm²','127 cm²','128 cm²','129 cm²'], 'correct'=>2],
                ['prompt'=>'12³ = ?', 'options'=>['1726','1727','1728','1729'], 'correct'=>2],
                ['prompt'=>'Simpangan data 5,6,7,8,9 dari rata-ratanya = ?', 'options'=>['√2','√3','√4 = 2','√5'], 'correct'=>2],
            ];
        }

        // ── IPA ───────────────────────────────────────────────────────────────
        if ($title === 'IPA') {
            $gradeData = [
                1 => [
                    ['prompt'=>'Hewan yang memiliki bulu adalah...', 'options'=>['Ikan','Burung','Katak','Ular'], 'correct'=>1],
                    ['prompt'=>'Tumbuhan membutuhkan sinar matahari untuk...', 'options'=>['Tidur','Fotosintesis','Bergerak','Minum'], 'correct'=>1],
                    ['prompt'=>'Bagian tubuh yang digunakan untuk melihat adalah...', 'options'=>['Telinga','Hidung','Mata','Mulut'], 'correct'=>2],
                    ['prompt'=>'Air mendidih berubah menjadi...', 'options'=>['Es','Uap air','Tanah','Kayu'], 'correct'=>1],
                    ['prompt'=>'Hewan yang tinggal di air adalah...', 'options'=>['Kucing','Anjing','Ikan','Burung'], 'correct'=>2],
                    ['prompt'=>'Buah-buahan memiliki rasa...', 'options'=>['Hanya manis','Manis, asam, atau pahit','Hanya pahit','Tidak berasa'], 'correct'=>1],
                    ['prompt'=>'Udara yang kita hirup disebut...', 'options'=>['Oksigen','Nitrogen','Karbon dioksida','Hidrogen'], 'correct'=>0],
                    ['prompt'=>'Daun berwarna hijau karena mengandung...', 'options'=>['Klorofil','Air','Mineral','Udara'], 'correct'=>0],
                    ['prompt'=>'Akar tanaman berfungsi untuk...', 'options'=>['Membuat makanan','Menyerap air','Bernapas','Bergerak'], 'correct'=>1],
                    ['prompt'=>'Hewan yang bertelur adalah...', 'options'=>['Anjing','Kucing','Ayam','Sapi'], 'correct'=>2],
                    ['prompt'=>'Benda yang bisa larut dalam air adalah...', 'options'=>['Pasir','Garam','Batu','Kayu'], 'correct'=>1],
                    ['prompt'=>'Sumber cahaya alami adalah...', 'options'=>['Lampu','Lilin','Matahari','Senter'], 'correct'=>2],
                    ['prompt'=>'Bagian yang paling penting dari tumbuhan adalah...', 'options'=>['Dahan','Bunga','Akar','Daun'], 'correct'=>2],
                    ['prompt'=>'Hewan pemakan daging disebut...', 'options'=>['Herbivora','Karnivora','Omnivora','Insektivora'], 'correct'=>1],
                    ['prompt'=>'Benda yang berwujud cair adalah...', 'options'=>['Batu','Kayu','Air','Asap'], 'correct'=>2],
                    ['prompt'=>'Bintang yang terdekat dari bumi adalah...', 'options'=>['Bulan','Matahari','Mars','Venus'], 'correct'=>1],
                    ['prompt'=>'Hewan yang bisa terbang adalah...', 'options'=>['Anjing','Kucing','Burung','Ular'], 'correct'=>2],
                    ['prompt'=>'Air berubah menjadi es karena...', 'options'=>['Dipanaskan','Didinginkan','Ditekan','Dicampur'], 'correct'=>1],
                    ['prompt'=>'Tulang berfungsi untuk...', 'options'=>['Memompa darah','Menyerap makanan','Menyokong tubuh','Mengatur suhu'], 'correct'=>2],
                    ['prompt'=>'Cuaca yang ditandai dengan banyak awan gelap adalah...', 'options'=>['Cerah','Berawan','Mendung dan hujan','Berangin'], 'correct'=>2],
                ],
                5 => [
                    ['prompt'=>'Sistem peredaran darah manusia terdiri dari...', 'options'=>['Jantung saja','Jantung dan pembuluh darah','Paru-paru saja','Otak dan jantung'], 'correct'=>1],
                    ['prompt'=>'Fotosintesis menghasilkan...', 'options'=>['CO₂ dan air','O₂ dan glukosa','H₂ dan nitrogen','Nitrogen dan air'], 'correct'=>1],
                    ['prompt'=>'Planet terdekat dari matahari adalah...', 'options'=>['Venus','Mars','Merkurius','Bumi'], 'correct'=>2],
                    ['prompt'=>'Perpindahan panas secara langsung disebut...', 'options'=>['Konveksi','Radiasi','Konduksi','Evaporasi'], 'correct'=>2],
                    ['prompt'=>'Ekosistem sawah terdiri dari...', 'options'=>['Laut dan ikan','Padi, tikus, elang, dan faktor abiotik','Hutan dan pohon','Gurun dan unta'], 'correct'=>1],
                    ['prompt'=>'Jenis batuan yang terbentuk dari lava adalah...', 'options'=>['Sedimen','Metamorf','Beku','Organik'], 'correct'=>2],
                    ['prompt'=>'Organ pencernaan yang menghasilkan enzim pepsin adalah...', 'options'=>['Usus halus','Lambung','Pankreas','Hati'], 'correct'=>1],
                    ['prompt'=>'Gaya magnet paling kuat di...', 'options'=>['Tengah magnet','Kutub magnet','Di sekitar magnet','Jauh dari magnet'], 'correct'=>1],
                    ['prompt'=>'Adaptasi hewan di kutub adalah...', 'options'=>['Kulit tipis','Bulu lebat dan tebal','Warna cerah','Tubuh kecil'], 'correct'=>1],
                    ['prompt'=>'Ciri-ciri makhluk hidup yang membedakan dari benda mati...', 'options'=>['Bergerak saja','Bernafas, tumbuh, berkembang biak, bergerak','Berwarna saja','Keras'], 'correct'=>1],
                    ['prompt'=>'Lapisan bumi yang paling luar disebut...', 'options'=>['Mantel','Inti','Kerak','Hidrosfer'], 'correct'=>2],
                    ['prompt'=>'Energi angin diubah menjadi listrik menggunakan...', 'options'=>['Panel surya','Bendungan','Turbin angin','Generator uap'], 'correct'=>2],
                    ['prompt'=>'Batang dikotil memiliki ciri...', 'options'=>['Tidak berbatang','Berkambium','Berbuku-buku','Berongga'], 'correct'=>1],
                    ['prompt'=>'Simbiosis mutualisme adalah...', 'options'=>['Satu untung satu rugi','Keduanya untung','Keduanya tidak terpengaruh','Satu mati'], 'correct'=>1],
                    ['prompt'=>'Hubungan predator dan mangsa disebut...', 'options'=>['Simbiosis','Rantai makanan (predasi)','Kompetisi','Kooperasi'], 'correct'=>1],
                    ['prompt'=>'Perubahan wujud cair menjadi gas disebut...', 'options'=>['Membeku','Mencair','Menguap','Mengembun'], 'correct'=>2],
                    ['prompt'=>'Benda yang dapat menghantarkan listrik disebut...', 'options'=>['Isolator','Konduktor','Semikonduktor','Kapasitor'], 'correct'=>1],
                    ['prompt'=>'Alat pernapasan utama manusia adalah...', 'options'=>['Jantung','Ginjal','Paru-paru','Hati'], 'correct'=>2],
                    ['prompt'=>'Efek rumah kaca disebabkan oleh...', 'options'=>['CO₂ berlebih di atmosfer','O₂ berlebih','Angin','Air laut'], 'correct'=>0],
                    ['prompt'=>'Sumber energi terbarukan adalah...', 'options'=>['Batu bara','Minyak bumi','Gas alam','Energi matahari'], 'correct'=>3],
                ],
            ];

            $fallback = [
                ['prompt'=>'Apa fungsi daun pada tumbuhan?', 'options'=>['Menyerap air','Fotosintesis','Menyokong tubuh','Berkembang biak'], 'correct'=>1],
                ['prompt'=>'Pernapasan manusia menggunakan organ...', 'options'=>['Jantung','Ginjal','Paru-paru','Hati'], 'correct'=>2],
                ['prompt'=>'Hewan yang berdarah panas adalah...', 'options'=>['Ikan','Katak','Ular','Burung'], 'correct'=>3],
                ['prompt'=>'Energi panas matahari termasuk energi...', 'options'=>['Listrik','Kimia','Cahaya dan panas','Gerak'], 'correct'=>2],
                ['prompt'=>'Sistem tata surya berpusat pada...', 'options'=>['Bumi','Bulan','Matahari','Pluto'], 'correct'=>2],
                ['prompt'=>'Akar, batang, daun adalah bagian dari...', 'options'=>['Hewan','Tumbuhan','Manusia','Mineral'], 'correct'=>1],
                ['prompt'=>'Magnet menarik benda yang terbuat dari...', 'options'=>['Plastik','Kayu','Besi','Karet'], 'correct'=>2],
                ['prompt'=>'Cahaya matahari dapat diubah menjadi listrik oleh...', 'options'=>['Turbin','Panel surya','Generator','Baterai'], 'correct'=>1],
                ['prompt'=>'Pemanasan global menyebabkan...', 'options'=>['Salju bertambah','Es kutub mencair','Udara dingin','Laut menyusut'], 'correct'=>1],
                ['prompt'=>'Gaya gravitasi membuat benda...', 'options'=>['Terbang','Jatuh ke bawah','Melayang','Berputar'], 'correct'=>1],
                ['prompt'=>'Sel adalah satuan terkecil dari...', 'options'=>['Benda mati','Makhluk hidup','Mineral','Udara'], 'correct'=>1],
                ['prompt'=>'Rantai makanan dimulai dari...', 'options'=>['Hewan herbivora','Produsen (tumbuhan)','Konsumen','Dekomposer'], 'correct'=>1],
                ['prompt'=>'Konduksi panas terjadi pada...', 'options'=>['Gas','Cair','Padat','Semua benda'], 'correct'=>2],
                ['prompt'=>'Sumber protein hewani adalah...', 'options'=>['Tahu','Tempe','Daging ikan','Kacang'], 'correct'=>2],
                ['prompt'=>'Gaya gesek terjadi antara dua...', 'options'=>['Magnet','Permukaan yang bersentuhan','Benda bermuatan listrik','Planet'], 'correct'=>1],
                ['prompt'=>'Bunyi dapat merambat melalui...', 'options'=>['Ruang hampa','Zat padat, cair, dan gas','Hanya gas','Hanya padat'], 'correct'=>1],
                ['prompt'=>'Proses metamorfosis sempurna terjadi pada...', 'options'=>['Belalang','Kecoa','Kupu-kupu','Jangkrik'], 'correct'=>2],
                ['prompt'=>'Daur air dimulai dari...', 'options'=>['Hujan','Penguapan (evaporasi)','Awan','Infiltrasi'], 'correct'=>1],
                ['prompt'=>'Tulang manusia berfungsi sebagai...', 'options'=>['Menghasilkan energi','Penyokong dan pelindung organ','Menyaring darah','Memompa darah'], 'correct'=>1],
                ['prompt'=>'Bahan bakar fosil berasal dari...', 'options'=>['Air','Udara','Sisa makhluk hidup jutaan tahun lalu','Batuan biasa'], 'correct'=>2],
            ];

            return $gradeData[$grade] ?? $fallback;
        }

        // ── BAHASA INDONESIA ──────────────────────────────────────────────────
        if ($title === 'Bahasa Indonesia') {
            $biData = [
                1 => [
                    ['prompt'=>'Huruf pertama dalam alfabet adalah...', 'options'=>['B','A','C','D'], 'correct'=>1],
                    ['prompt'=>'"Saya pergi ke sekolah." Kata "saya" adalah...', 'options'=>['Kata benda','Kata ganti','Kata kerja','Kata sifat'], 'correct'=>1],
                    ['prompt'=>'Kalimat yang baik diawali dengan huruf...', 'options'=>['Kecil','Kapital','Miring','Tebal'], 'correct'=>1],
                    ['prompt'=>'Lawan kata "besar" adalah...', 'options'=>['Tinggi','Kecil','Panjang','Lebar'], 'correct'=>1],
                    ['prompt'=>'Kata tanya "di mana" digunakan untuk menanyakan...', 'options'=>['Waktu','Tempat','Orang','Cara'], 'correct'=>1],
                    ['prompt'=>'Huruf vokal adalah...', 'options'=>['a, e, i, o, u','b, c, d, f','g, h, j, k','m, n, p, q'], 'correct'=>0],
                    ['prompt'=>'Judul buku ditulis dengan huruf...', 'options'=>['Semua huruf kecil','Huruf kapital di awal setiap kata penting','Semua huruf kapital','Huruf miring'], 'correct'=>1],
                    ['prompt'=>'Kalimat tanya diakhiri dengan tanda...', 'options'=>['Titik (.)','Koma (,)','Tanya (?)','Seru (!)'], 'correct'=>2],
                    ['prompt'=>'Sinonim kata "rumah" adalah...', 'options'=>['Toko','Gedung','Tempat tinggal','Kantor'], 'correct'=>2],
                    ['prompt'=>'Kata "berlari" mengandung awalan...', 'options'=>['ber-','me-','di-','ter-'], 'correct'=>0],
                    ['prompt'=>'Kalimat perintah diakhiri dengan tanda...', 'options'=>['Titik (.)','Koma (,)','Tanya (?)','Seru (!)'], 'correct'=>3],
                    ['prompt'=>'Antonim kata "rajin" adalah...', 'options'=>['Giat','Tekun','Malas','Semangat'], 'correct'=>2],
                    ['prompt'=>'Paragraf adalah kumpulan beberapa...', 'options'=>['Huruf','Kata','Kalimat','Buku'], 'correct'=>2],
                    ['prompt'=>'"Singa adalah raja hutan" adalah contoh...', 'options'=>['Fakta','Opini','Majas personifikasi','Majas metafora'], 'correct'=>3],
                    ['prompt'=>'Kata "menulis" berasal dari kata dasar...', 'options'=>['Menu','Tulis','Nulis','Ulis'], 'correct'=>1],
                    ['prompt'=>'Jenis karangan yang mengisahkan sebuah cerita disebut...', 'options'=>['Deskripsi','Eksposisi','Narasi','Argumentasi'], 'correct'=>2],
                    ['prompt'=>'Kalimat majemuk adalah kalimat yang terdiri dari...', 'options'=>['Satu klausa','Dua klausa atau lebih','Tiga subjek','Empat predikat'], 'correct'=>1],
                    ['prompt'=>'Puisi berjudul "Ibu Pertiwi" bercerita tentang...', 'options'=>['Ibu kandung','Teman','Tanah air','Sekolah'], 'correct'=>2],
                    ['prompt'=>'Teks prosedur berisi...', 'options'=>['Cerita fiksi','Langkah-langkah cara melakukan sesuatu','Pendapat pribadi','Deskripsi tempat'], 'correct'=>1],
                    ['prompt'=>'Membaca cepat untuk mendapatkan ide pokok disebut...', 'options'=>['Membaca intensif','Skimming','Scanning','Membaca nyaring'], 'correct'=>1],
                ],
            ];
            $biFallback = [
                ['prompt'=>'Ide pokok sebuah paragraf biasanya terletak di...', 'options'=>['Kalimat pertama atau terakhir','Kalimat tengah saja','Di semua kalimat','Judul teks'], 'correct'=>0],
                ['prompt'=>'Teks deskripsi bertujuan untuk...', 'options'=>['Menghibur','Meyakinkan','Menjelaskan langkah','Menggambarkan objek secara detail'], 'correct'=>3],
                ['prompt'=>'"Bunga itu tertawa riang." adalah majas...', 'options'=>['Metafora','Simile','Personifikasi','Hiperbola'], 'correct'=>2],
                ['prompt'=>'Kata yang berlawanan makna disebut...', 'options'=>['Sinonim','Antonim','Homonim','Polisemi'], 'correct'=>1],
                ['prompt'=>'Tanda koma (,) digunakan untuk...', 'options'=>['Mengakhiri kalimat','Memisahkan anak kalimat','Pertanyaan','Seruan'], 'correct'=>1],
                ['prompt'=>'Kalimat efektif adalah kalimat yang...', 'options'=>['Panjang dan rumit','Jelas, singkat, dan mudah dipahami','Penuh kata sifat','Tanpa subjek'], 'correct'=>1],
                ['prompt'=>'Awalan "me-" pada kata "membaca" berfungsi sebagai...', 'options'=>['Kata benda','Kata kerja aktif','Kata sifat','Kata keterangan'], 'correct'=>1],
                ['prompt'=>'Surat resmi menggunakan bahasa yang...', 'options'=>['Santai','Gaul','Baku dan formal','Puitis'], 'correct'=>2],
                ['prompt'=>'Pengarang novel disebut...', 'options'=>['Penyair','Novelis','Dramawan','Jurnalis'], 'correct'=>1],
                ['prompt'=>'"Secepat kilat" adalah contoh majas...', 'options'=>['Personifikasi','Metafora','Hiperbola','Simile'], 'correct'=>3],
                ['prompt'=>'Gagasan utama teks persuasi adalah untuk...', 'options'=>['Menghibur','Mempengaruhi pembaca','Menginformasikan fakta','Mendeskripsikan'], 'correct'=>1],
                ['prompt'=>'Kata baku yang benar adalah...', 'options'=>['Ijin','Apotik','Izin','Nasehat'], 'correct'=>2],
                ['prompt'=>'Pantun terdiri dari...', 'options'=>['2 baris','3 baris','4 baris','6 baris'], 'correct'=>2],
                ['prompt'=>'Pada pantun, baris ke-3 dan ke-4 disebut...', 'options'=>['Sampiran','Isi','Judul','Rima'], 'correct'=>1],
                ['prompt'=>'Kata penghubung "karena" menyatakan...', 'options'=>['Tujuan','Syarat','Sebab','Akibat'], 'correct'=>2],
                ['prompt'=>'Topik dan ide pokok adalah bagian dari struktur...', 'options'=>['Kalimat','Paragraf','Kata','Huruf'], 'correct'=>1],
                ['prompt'=>'Wawancara adalah kegiatan tanya jawab yang bertujuan...', 'options'=>['Bermain','Mendapatkan informasi','Menghibur','Berlatih'], 'correct'=>1],
                ['prompt'=>'Kata "tercepat" mengandung akhiran...', 'options'=>['-kan','-an','-i','-ter adalah awalan'], 'correct'=>3],
                ['prompt'=>'Berita harus memenuhi unsur 5W+1H, H adalah...', 'options'=>['Who','Where','When','How'], 'correct'=>3],
                ['prompt'=>'Karya sastra berbentuk dialog dan dimainkan di panggung adalah...', 'options'=>['Novel','Puisi','Drama','Cerpen'], 'correct'=>2],
            ];
            return $biData[$grade] ?? $biFallback;
        }

        // ── PKn ───────────────────────────────────────────────────────────────
        $pknData = [
            ['prompt'=>'Dasar negara Indonesia adalah...', 'options'=>['UUD 1945','Pancasila','Bhinneka Tunggal Ika','NKRI'], 'correct'=>1],
            ['prompt'=>'Sila pertama Pancasila berbunyi...', 'options'=>['Kemanusiaan yang adil','Ketuhanan Yang Maha Esa','Persatuan Indonesia','Kerakyatan'], 'correct'=>1],
            ['prompt'=>'Semboyan negara Indonesia adalah...', 'options'=>['Bhineka Tunggal Ika','Garuda Pancasila','UUD 1945','NKRI Harga Mati'], 'correct'=>0],
            ['prompt'=>'Bendera Indonesia berwarna...', 'options'=>['Merah putih biru','Merah dan putih','Hijau dan putih','Kuning dan merah'], 'correct'=>1],
            ['prompt'=>'Kepala negara Indonesia adalah...', 'options'=>['Perdana Menteri','Raja','Presiden','Sultan'], 'correct'=>2],
            ['prompt'=>'Pemilihan umum dilakukan untuk memilih...', 'options'=>['Ketua RT','Presiden dan wakil rakyat','Kepala sekolah','Ketua kelas'], 'correct'=>1],
            ['prompt'=>'Hak anak untuk mendapatkan pendidikan diatur dalam...', 'options'=>['Undang-Undang','Peraturan desa','Adat istiadat','Keputusan pribadi'], 'correct'=>0],
            ['prompt'=>'Sikap menghormati perbedaan suku, agama, dan ras disebut...', 'options'=>['Fanatisme','Toleransi','Diskriminasi','Rasisme'], 'correct'=>1],
            ['prompt'=>'Lagu kebangsaan Indonesia adalah...', 'options'=>['Garuda Pancasila','Indonesia Raya','Bagimu Negeri','Rayuan Pulau Kelapa'], 'correct'=>1],
            ['prompt'=>'Hari Kemerdekaan Indonesia diperingati setiap...', 'options'=>['17 Agustus','17 Juli','17 September','17 Oktober'], 'correct'=>0],
            ['prompt'=>'Kewajiban warga negara di sekolah adalah...', 'options'=>['Bermain saja','Belajar dengan sungguh-sungguh','Tidur di kelas','Tidak mengerjakan tugas'], 'correct'=>1],
            ['prompt'=>'Musyawarah mufakat merupakan pengamalan sila ke...', 'options'=>['1','2','3','4'], 'correct'=>3],
            ['prompt'=>'Ciri-ciri demokrasi Pancasila adalah...', 'options'=>['Keputusan satu orang','Berdasarkan musyawarah dan mufakat','Pemimpin berkuasa mutlak','Keputusan diundi'], 'correct'=>1],
            ['prompt'=>'Proklamasi kemerdekaan Indonesia dibacakan oleh...', 'options'=>['Soeharto dan Habibie','Soekarno dan Hatta','Sultan Hamengkubuwono','Jenderal Sudirman'], 'correct'=>1],
            ['prompt'=>'Pasal 31 UUD 1945 mengatur tentang...', 'options'=>['Agama','Pendidikan','Pertahanan','Ekonomi'], 'correct'=>1],
            ['prompt'=>'Gotong royong adalah nilai luhur yang mencerminkan sila ke...', 'options'=>['2','3','4','5'], 'correct'=>1],
            ['prompt'=>'Lembaga legislatif di Indonesia adalah...', 'options'=>['Presiden','DPR/MPR','Mahkamah Agung','KPK'], 'correct'=>1],
            ['prompt'=>'Hak memilih dalam pemilu diperoleh warga negara yang berumur...', 'options'=>['15 tahun','16 tahun','17 tahun','18 tahun'], 'correct'=>2],
            ['prompt'=>'Tujuan nasional Indonesia tercantum dalam...', 'options'=>['Pasal 1 UUD','Pembukaan UUD 1945','Pancasila sila 5','Bhinneka Tunggal Ika'], 'correct'=>1],
            ['prompt'=>'Pemerintahan daerah tingkat II dipimpin oleh...', 'options'=>['Presiden','Gubernur','Bupati/Walikota','Camat'], 'correct'=>2],
        ];
        return $pknData;
    }
}
