# Aplikasi CAT SD - Ujian Berbasis Komputer Anak SD 🎒✏️

Aplikasi **CAT (Computer Assisted Test)** khusus untuk siswa Sekolah Dasar (SD) yang dibangun menggunakan **Android Jetpack Compose & Material 3** dalam bahasa **Kotlin**.

Designed with ❤️ for Elementary School Students (Kelas 1 s/d 6 SD).

---

## ✨ Fitur Utama

- 📱 **Bottom Navigation Bar**: Navigasi cepat antara Tab **Beranda (Ujian)**, **Riwayat Ujian**, dan **Profil Siswa**.
- 🌙 **Dual Theme System (Proper Light & Dark Mode)**:
  - **Mode Terang (Light Mode)**: Desain pastel bersih (`#F8FAFC`) dengan kontras teks Slate.
  - **Mode Gelap (Dark Mode)**: Warna latar Slate 900 (`#0F172A`), kartu surface Slate 800 (`#1E293B`), dan aksen Indigo/Cyan yang nyaman untuk mata anak saat malam hari.
  - Pengatur tema di Profil (*Light*, *Dark*, dan *Follow System*).
- 👤 **Profil Siswa & Avatar Lucu**:
  - Nama siswa dapat diubah secara dinamis.
  - Pilihan Karakter Avatar (Kelinci Ceria, Beruang Pintar, Robot Cerdas, Astronot Cilik, Juara Super).
  - Pemilih Tingkat Kelas SD aktif (Kelas 1 - 6 SD).
- 📚 **20 Soal per Mata Pelajaran (Kelas 1 s/d 6 SD)**:
  - Bank soal spesifik untuk 4 mata pelajaran utama: **Matematika**, **IPA (Sains)**, **Bahasa Indonesia**, dan **Pancasila / PKn**.
  - Kurikulum dan tingkat kesulitan soal disesuaikan khusus untuk tiap kelas (Kelas 1 hingga Kelas 6 SD).
- 📝 **Interaktif Exam CAT Screen**:
  - **Real-time Countdown Timer**: Indikator visual waktu (Hijau $\rightarrow$ Oranye $\rightarrow$ Merah).
  - **Papan Grid Navigasi 20 Soal**: Indikator status nomor soal (Terjawab, Ragu-Ragu, Belum Terisi).
  - **Fitur Tombol Ragu-Ragu** & Pilihan Ganda (A, B, C, D) berdesain pill yang besar dan ramah anak.
- 📊 **Hasil & Apresiasi**:
  - Skor Akhir (0–100) dan **Apresiasi 1–3 Bintang**.
  - Statistik detail: Benar, Salah, Kosong, dan Durasi Waktu Pengerjaan.
- 💡 **Pembahasan Soal Interaktif**: Penjelasan langkah demi langkah lengkap untuk 20 soal.

---

## 🚀 Struktur Proyek

```text
c:/Users/muham/AndroidStudioProjects/CAT/
├── frontend/
│   ├── app/
│   │   └── src/main/java/com/tanilink/cat/
│   │       ├── data/
│   │       │   └── SampleData.kt          # Bank Soal 20 per Mapel (Kelas 1-6 SD) & Avatar
│   │       ├── model/
│   │       │   └── QuestionModel.kt       # Data Models & Enums (AppThemeOption, MainTab)
│   │       ├── ui/
│   │       │   ├── screens/
│   │       │   │   ├── HomeScreen.kt      # Beranda Katalag Mapel & Filter Kelas
│   │       │   │   ├── ExamScreen.kt      # Layar Ujian CAT (Timer, Grid 20 Soal, Ragu-Ragu)
│   │       │   │   ├── ResultScreen.kt    # Layar Skor & Bintang Apresiasi
│   │       │   │   ├── ReviewScreen.kt    # Pembahasan Soal Detail
│   │       │   │   ├── HistoryScreen.kt   # Riwayat & Evaluasi Nilai
│   │       │   │   └── ProfileScreen.kt   # Editor Nama, Avatar & Light/Dark Theme
│   │       │   └── theme/
│   │       │       ├── Color.kt           # Color Palette (Slate Dark & Pastel Light)
│   │       │       └── Theme.kt           # Material 3 Light & Dark ColorSchemes
│   │       ├── viewmodel/
│   │       │   └── ExamViewModel.kt       # State Manager Ujian, Timer & Scoring
│   │       └── MainActivity.kt            # NavigationBar Compose & Theme Controller
└── README.md
```

---

## 🛠️ Cara Menjalankan

1. Buka folder `frontend` di **Android Studio**.
2. Pastikan Android SDK 36 dan JDK 11/17 telah terinstal.
3. Jalankan command kompilasi:
   ```bash
   ./gradlew assembleDebug
   ```
4. Jalankan aplikasi pada Emulator Android atau Perangkat HP (Min SDK 33+).

---

## 📄 Lisensi

Pengembangan aplikasi CAT SD ini diperuntukkan untuk sarana simulasi dan latihan ujian anak-anak Sekolah Dasar.
