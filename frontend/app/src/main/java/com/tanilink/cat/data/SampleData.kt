package com.tanilink.cat.data

import androidx.compose.ui.graphics.Color
import com.tanilink.cat.model.ExamSubject
import com.tanilink.cat.model.Question
import com.tanilink.cat.model.UserAvatar

object SampleData {

    val avatars = listOf(
        UserAvatar("av1", "Kelinci Ceria", "Pets", Color(0xFFFCE4EC)),
        UserAvatar("av2", "Beruang Pintar", "EmojiEmotions", Color(0xFFFFF8E1)),
        UserAvatar("av3", "Robot Cerdas", "SmartToy", Color(0xFFE1F5FE)),
        UserAvatar("av4", "Astronot Cilik", "RocketLaunch", Color(0xFFA5B4FC)),
        UserAvatar("av5", "Juara Super", "SportsEsports", Color(0xFFDCFCE7))
    )

    val subjects = listOf(
        ExamSubject(
            id = "mtk_sd",
            title = "Matematika",
            iconName = "Calculate",
            primaryColor = Color(0xFFFF6F00), // Vibrant Orange
            secondaryColor = Color(0xFFFFF3E0),
            questionCount = 20,
            durationMinutes = 20,
            description = "Latihan 20 soal hitungan angka, pecahan, dan bangun datar!"
        ),
        ExamSubject(
            id = "ipa_sd",
            title = "IPA (Sains)",
            iconName = "Science",
            primaryColor = Color(0xFF2E7D32), // Vibrant Green
            secondaryColor = Color(0xFFE8F5E9),
            questionCount = 20,
            durationMinutes = 20,
            description = "Latihan 20 soal mahluk hidup, alam, dan sains ceria."
        ),
        ExamSubject(
            id = "bind_sd",
            title = "Bahasa Indonesia",
            iconName = "MenuBook",
            primaryColor = Color(0xFF1565C0), // Bright Blue
            secondaryColor = Color(0xFFE3F2FD),
            questionCount = 20,
            durationMinutes = 20,
            description = "Latihan 20 soal bacaan, ide pokok, dan kata baku."
        ),
        ExamSubject(
            id = "pkn_sd",
            title = "Pancasila & PKn",
            iconName = "Security",
            primaryColor = Color(0xFFC62828), // Bold Red
            secondaryColor = Color(0xFFFFEBEE),
            questionCount = 20,
            durationMinutes = 20,
            description = "Latihan 20 soal nilai Pancasila, hak, dan kewajiban."
        )
    )

    fun getQuestionsForSubjectAndGrade(subjectId: String, grade: Int): List<Question> {
        val baseList = getBase5Questions(subjectId, grade)
        val expandedList = mutableListOf<Question>()
        
        // Build 20 questions by extending base questions with variations and explanations
        for (i in 0 until 20) {
            val baseQ = baseList[i % baseList.size]
            val qId = i + 1
            if (i < baseList.size) {
                expandedList.add(baseQ.copy(id = qId))
            } else {
                val multiplier = (i / baseList.size) + 1
                expandedList.add(generateVariantQuestion(baseQ, qId, multiplier, grade))
            }
        }
        return expandedList
    }

    private fun generateVariantQuestion(base: Question, newId: Int, multiplier: Int, grade: Int): Question {
        return when (base.subjectId) {
            "mtk_sd" -> {
                val n1 = (5 * multiplier) + grade
                val n2 = (3 * multiplier) + (newId % 4)
                val ans = n1 + n2
                Question(
                    id = newId,
                    prompt = "Berapakah hasil penjumlahan dari $n1 + $n2?",
                    options = listOf("${ans - 2}", "$ans", "${ans + 3}", "${ans + 5}"),
                    correctAnswerIndex = 1,
                    explanation = "Langkah pengerjaan: $n1 ditambahkan $n2 hasilnya adalah $ans.",
                    subjectId = base.subjectId,
                    gradeLevel = grade
                )
            }
            "ipa_sd" -> {
                val topics = listOf(
                    "Mengapa tumbuhan memerlukan air?" to "Air diserap akar untuk mengangkut nutrisi dan fotosintesis.",
                    "Apa fungsi paru-paru pada manusia?" to "Paru-paru tempat pertukaran udara oksigen dan karbondioksida.",
                    "Benda gas mengisi ruangan karena..." to "Partikel benda gas bebas bergerak mengisi wadahnya.",
                    "Matahari terbit dari sebelah..." to "Matahari selalu terbit dari arah timur."
                )
                val t = topics[newId % topics.size]
                Question(
                    id = newId,
                    prompt = "Soal Sains Nomor $newId: ${t.first}",
                    options = listOf("Untuk berkembang", "Jawaban Benar", "Tidak ada pengaruh", "Salah semua"),
                    correctAnswerIndex = 1,
                    explanation = t.second,
                    subjectId = base.subjectId,
                    gradeLevel = grade
                )
            }
            "bind_sd" -> {
                Question(
                    id = newId,
                    prompt = "Soal Bahasa Indonesia #$newId: Antonim atau lawan kata dari 'Tinggi' adalah...",
                    options = listOf("Panjang", "Kecil", "Rendah", "Lebar"),
                    correctAnswerIndex = 2,
                    explanation = "Lawan kata dari 'Tinggi' adalah 'Rendah'.",
                    subjectId = base.subjectId,
                    gradeLevel = grade
                )
            }
            else -> { // PKn
                Question(
                    id = newId,
                    prompt = "Soal PKn Nomor $newId: Pancasila terdiri dari berapa sila sebagai dasar negara?",
                    options = listOf("3 Sila", "4 Sila", "5 Sila", "6 Sila"),
                    correctAnswerIndex = 2,
                    explanation = "Pancasila memiliki 5 Sila sebagai dasar negara Indonesia.",
                    subjectId = base.subjectId,
                    gradeLevel = grade
                )
            }
        }
    }

    private fun getBase5Questions(subjectId: String, grade: Int): List<Question> {
        return when (subjectId) {
            "mtk_sd" -> when (grade) {
                1 -> listOf(
                    Question(1, "Berapakah hasil dari 5 + 3?", listOf("7", "8", "9", "10"), 1, "5 disimpan di mulut, tambah 3 jari = 8.", "mtk_sd", 1),
                    Question(2, "Berapakah hasil dari 10 - 4?", listOf("5", "6", "7", "8"), 1, "10 dikurangi 4 hasilnya 6.", "mtk_sd", 1),
                    Question(3, "Manakah angka yang lebih besar antara 12 dan 18?", listOf("12", "18", "Sama", "Tidak tahu"), 1, "Angka 18 terletak setelah 12.", "mtk_sd", 1),
                    Question(4, "Benda berbentuk roda sepeda adalah...", listOf("Segitiga", "Persegi", "Lingkaran", "Kubus"), 2, "Roda sepeda berbentuk Lingkaran.", "mtk_sd", 1),
                    Question(5, "Urutan angka setelah 7, 8, 9 adalah...", listOf("10", "11", "6", "12"), 0, "Setelah 9 adalah 10.", "mtk_sd", 1)
                )
                2 -> listOf(
                    Question(1, "Hasil dari 25 + 14 adalah...", listOf("38", "39", "40", "41"), 1, "25 + 14 = 39.", "mtk_sd", 2),
                    Question(2, "Hasil dari 50 - 18 adalah...", listOf("32", "34", "30", "28"), 0, "50 - 18 = 32.", "mtk_sd", 2),
                    Question(3, "Hasil dari 4 x 3 adalah...", listOf("7", "10", "12", "14"), 2, "4 + 4 + 4 = 12.", "mtk_sd", 2),
                    Question(4, "Bangun datar 3 sisi dan 3 sudut adalah...", listOf("Persegi", "Segitiga", "Lingkaran", "Trapesium"), 1, "Segitiga memiliki 3 sisi.", "mtk_sd", 2),
                    Question(5, "Jarum pendek di 3, jarum panjang di 12 menunjukkan...", listOf("Pukul 03.00", "Pukul 12.00", "Pukul 06.00", "Pukul 09.00"), 0, "Pukul 03.00 tepat.", "mtk_sd", 2)
                )
                3 -> listOf(
                    Question(1, "Hasil dari 125 + 85 adalah...", listOf("200", "210", "215", "220"), 1, "125 + 85 = 210.", "mtk_sd", 3),
                    Question(2, "Hasil dari 8 x 7 adalah...", listOf("48", "54", "56", "64"), 2, "8 x 7 = 56.", "mtk_sd", 3),
                    Question(3, "Hasil dari 45 ÷ 5 adalah...", listOf("7", "8", "9", "10"), 2, "45 ÷ 5 = 9.", "mtk_sd", 3),
                    Question(4, "Keliling persegi sisi 5 cm adalah...", listOf("15 cm", "20 cm", "25 cm", "30 cm"), 1, "4 x 5 cm = 20 cm.", "mtk_sd", 3),
                    Question(5, "Pecahan satu per dua dituliskan...", listOf("1/3", "1/4", "1/2", "2/1"), 2, "Ditulis 1/2.", "mtk_sd", 3)
                )
                4 -> listOf(
                    Question(1, "Luas persegi panjang P=8 cm L=5 cm adalah...", listOf("13 cm²", "26 cm²", "40 cm²", "45 cm²"), 2, "8 x 5 = 40 cm².", "mtk_sd", 4),
                    Question(2, "FPB dari 8 dan 12 adalah...", listOf("2", "4", "6", "8"), 1, "FPB dari 8 dan 12 adalah 4.", "mtk_sd", 4),
                    Question(3, "Bentuk sederhana pecahan 2/4 adalah...", listOf("1/2", "1/3", "1/4", "3/4"), 0, "2/4 = 1/2.", "mtk_sd", 4),
                    Question(4, "Keliling segitiga sama sisi s=7 cm adalah...", listOf("14 cm", "21 cm", "28 cm", "35 cm"), 1, "7 + 7 + 7 = 21 cm.", "mtk_sd", 4),
                    Question(5, "Hasil dari 150 - (25 x 4) adalah...", listOf("50", "75", "100", "125"), 0, "25 x 4 = 100. 150 - 100 = 50.", "mtk_sd", 4)
                )
                5 -> listOf(
                    Question(1, "Hasil dari 250 + 175 - 120 adalah...", listOf("305", "315", "295", "325"), 0, "250 + 175 = 425. 425 - 120 = 305.", "mtk_sd", 5),
                    Question(2, "Luas persegi dengan sisi 12 cm adalah...", listOf("48 cm²", "144 cm²", "124 cm²", "96 cm²"), 1, "12 x 12 = 144 cm².", "mtk_sd", 5),
                    Question(3, "Pecahan paling sederhana dari 15/20 adalah...", listOf("1/2", "2/3", "3/4", "4/5"), 2, "15÷5 = 3, 20÷5 = 4 (3/4).", "mtk_sd", 5),
                    Question(4, "Harga 3 kg apel jika 1 kg Rp 25.000 adalah...", listOf("Rp 65.000", "Rp 70.000", "Rp 75.000", "Rp 80.000"), 2, "3 x Rp 25.000 = Rp 75.000.", "mtk_sd", 5),
                    Question(5, "FPB dari 12 dan 18 adalah...", listOf("2", "4", "6", "12"), 2, "FPB = 6.", "mtk_sd", 5)
                )
                else -> listOf(
                    Question(1, "Hasil dari (-15) + (25 x 2) adalah...", listOf("25", "35", "45", "50"), 1, "25 x 2 = 50. (-15) + 50 = 35.", "mtk_sd", 6),
                    Question(2, "Luas lingkaran r = 7 cm (π = 22/7) adalah...", listOf("44 cm²", "154 cm²", "308 cm²", "616 cm²"), 1, "(22/7) x 7 x 7 = 154 cm².", "mtk_sd", 6),
                    Question(3, "Volume kubus dengan rusuk 6 cm adalah...", listOf("36 cm³", "144 cm³", "216 cm³", "256 cm³"), 2, "6 x 6 x 6 = 216 cm³.", "mtk_sd", 6),
                    Question(4, "KPK dari 12 dan 18 adalah...", listOf("24", "36", "48", "72"), 1, "KPK = 36.", "mtk_sd", 6),
                    Question(5, "Rata-rata dari nilai 7, 8, 9, 8, 8 adalah...", listOf("7.5", "8.0", "8.2", "8.5"), 1, "40 ÷ 5 = 8.0.", "mtk_sd", 6)
                )
            }
            "ipa_sd" -> listOf(
                Question(1, "Hewan pemakan tumbuhan disebut...", listOf("Karnivora", "Herbivora", "Omnivora", "Insektivora"), 1, "Herbivora makan tumbuhan.", "ipa_sd", grade),
                Question(2, "Fotosintesis pada tumbuhan memerlukan bantuan sinar...", listOf("Bulan", "Matahari", "Lampu", "Api"), 1, "Sinar matahari menyinari klorofil.", "ipa_sd", grade),
                Question(3, "Perubahan wujud cair ke gas dinamakan...", listOf("Mencair", "Menguap", "Membeku", "Menyublim"), 1, "Air mendidih jadi uap = menguap.", "ipa_sd", grade),
                Question(4, "Organ yang memompa darah ke seluruh tubuh adalah...", listOf("Paru-paru", "Jantung", "Hati", "Lambung"), 1, "Jantung memompa darah.", "ipa_sd", grade),
                Question(5, "Tempat hidup ikan air tawar di...", listOf("Darat", "Sungai / Kolam", "Udara", "Pohon"), 1, "Ikan bernapas di air.", "ipa_sd", grade)
            )
            "bind_sd" -> listOf(
                Question(1, "Gagasan utama paragraf disebut...", listOf("Kalimat penjelas", "Ide pokok", "Judul", "Kesimpulan"), 1, "Ide pokok adalah inti paragraf.", "bind_sd", grade),
                Question(2, "Kata baku yang tepat adalah...", listOf("Apotik", "Apotek", "Apotekk", "Afotik"), 1, "Kata baku: Apotek.", "bind_sd", grade),
                Question(3, "Baris ke-3 dan ke-4 pantun berisi...", listOf("Sampiran", "Isi pantun", "Bait", "Sajak"), 1, "Baris 3-4 adalah isi.", "bind_sd", grade),
                Question(4, "Kalimat perintah diakhiri tanda...", listOf("Titik", "Tanya", "Seru (!)", "Koma"), 2, "Diakhiri tanda seru (!).", "bind_sd", grade),
                Question(5, "Lawan kata dari 'Dermawan' adalah...", listOf("Baik", "Pelit / Kikir", "Sombong", "Jujur"), 1, "Lawan dermawan adalah pelit.", "bind_sd", grade)
            )
            else -> listOf(
                Question(1, "Lambang Sila Ke-3 Pancasila adalah...", listOf("Bintang", "Rantai", "Pohon Beringin", "Kepala Banteng"), 2, "Pohon Beringin = Sila ke-3.", "pkn_sd", grade),
                Question(2, "Contoh Sila ke-1 di sekolah adalah...", listOf("Toleransi ibadah", "Musyawarah", "Piket kelas", "Jujur"), 0, "Menghormati teman beribadah.", "pkn_sd", grade),
                Question(3, "Sesuatu yang wajib dilaksanakan disebut...", listOf("Hak", "Kewajiban", "Hadiah", "Aturan"), 1, "Kewajiban harus ditunaikan.", "pkn_sd", grade),
                Question(4, "Bhinneka Tunggal Ika artinya...", listOf("Berbeda-beda tetap satu", "Bersatu kita teguh", "Maju terus", "Merdeka"), 0, "Berbeda-beda tetap satu jua.", "pkn_sd", grade),
                Question(5, "Mendapat kasih sayang orang tua adalah...", listOf("Kewajiban", "Hak anak", "Tugas", "Hukuman"), 1, "Hak anak dalam keluarga.", "pkn_sd", grade)
            )
        }
    }
}
