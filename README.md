# CAT SD - Aplikasi Ujian Berbasis Komputer & AI Proctoring Siswa SD 🎒✏️

Sistem **CAT (Computer Assisted Test)** modern dan terintegrasi untuk siswa Sekolah Dasar (Kelas 1 s/d 6 SD), dilengkapi dengan **AI Face Proctoring** cerdas berbasis Google ML Kit dan **Dashboard Manajemen Guru/Pengawas** berbasis Laravel Web.

---

## 🏗️ Arsitektur Proyek

Sistem CAT SD terdiri dari 2 komponen utama:

1. **Frontend (Android Native App)**:
   * **Framework**: Jetpack Compose & Material 3 (Kotlin)
   * **Vision AI**: Google ML Kit Face Detection (Deteksi fokus wajah, multi-wajah, dan orientasi sudut kepala)
   * **Camera**: AndroidX CameraX (Floating moveable camera preview)
   * **Local Storage**: SQLite Database (Riwayat ujian & soal) + SharedPreferences + Image Disk Cache
   * **Network**: RESTful API Client (Otomatis deteksi IP lokal/emulator)

2. **Backend (Laravel REST API & Web Dashboard)**:
   * **Framework**: Laravel 10.x (PHP 8.2+)
   * **Database**: MySQL 8.x
   * **Authentication**: JWT Bearer Token (`tymon/jwt-auth`)
   * **Web Admin Portal**: TailwindCSS + Alpine.js (Dashboard Pengawas & Bank Soal di `/admin`)

---

## ✨ Fitur-Fitur Utama

### 📱 Android Client (Siswa)
- 🔐 **Autentikasi Siswa & Guru**: Login fleksibel menggunakan **Nomor Induk / NISN** atau **Username** + Password.
- 🎨 **Modern Dual Theme**: Dukungan penuh **Light Mode** (pastel ramah anak) dan **Dark Mode** (slate hemat baterai & nyaman di mata).
- 👤 **Profil & Foto Siswa**:
  - Pilihan karakter avatar bawaan (Kelinci Ceria, Beruang Pintar, Robot Cerdas, Astronot Cilik, Juara Super).
  - Unggah foto profil kustom dengan **offline disk cache** (foto tetap tampil saat offline/terputus dari server).
- 📚 **Bank Soal Dinamis (Online Database)**:
  - Soal diambil langsung secara dinamis dari database MySQL per jenjang kelas (Kelas 1 - 6 SD).
  - Soal mendukung teks, gambar pertanyaan, opsi gambar pilihan ganda (A, B, C, D), dan pembahasan lengkap.
- 👁️ **AI Face Proctoring (Pengawas Otomatis)**:
  - **Deteksi Wajah**: Mendeteksi jika wajah siswa keluar dari layar (`NO_FACE`).
  - **Deteksi Menoleh**: Mendeteksi sudut kemiringan kepala (*head Euler angle Y/Z*) saat melirik (`LOOKING_AWAY`).
  - **Deteksi Kecurangan Multi-Wajah**: Mendeteksi kehadiran orang lain di depan layar (`MULTIPLE_FACES`).
  - **Deteksi Pindah Aplikasi**: Deteksi otomatis saat siswa membuka aplikasi lain (`SWITCH_TAB`).
  - **Deteksi Jawaban Kilat**: Mendeteksi klik jawaban terlalu cepat (< 2 detik) (`RAPID_ANSWERING`).
  - **Floating Camera Preview**: Kamera pengawas dapat dipindahkan ke 4 sudut layar dengan mudah.
  - **Auto-Lock Ujian**: Ujian otomatis terkunci saat pelanggaran mencapai batas maksimal (`max_violations`).
- 🔑 **Sistem Buka Ujian / Kode Remedi**:
  - Pengawas dapat membuka kembali ujian yang terkunci menggunakan **Kode Remedi** dinamis per kelas (`REMEDI1` s/d `REMEDI6`).
- 📊 **Hasil & Pembahasan Detail**:
  - Skor 0–100 dengan apresiasi bintang apresiatif.
  - Statistik jawaban Benar, Salah, dan Kosong.
  - Layar Pembahasan (*Review Screen*) langkah demi langkah untuk setiap nomor soal.

### 🖥️ Backend & Admin Dashboard (Guru & Pengawas)
- 📊 **Pusat Pantauan AI Proctoring Real-Time**:
  - Memantau log pelanggaran siswa yang terdeteksi saat ujian sedang berlangsung.
  - Auto-refresh otomatis setiap 10 detik.
- 🗃️ **Manajemen Mata Pelajaran & Bank Soal**:
  - Tambah, edit, dan hapus mata pelajaran per kelas.
  - Tambah, edit, dan hapus butir soal, opsi jawaban, gambar, dan kunci jawaban.
- 👥 **Manajemen Akun Siswa & Guru**:
  - Kelola NISN/Nomor Induk, username, password, dan hak akses admin.
  - Salin password sekali klik.
- ⚙️ **Pengaturan Kode Sistem**:
  - Pengaturan kode izin penggantian kelas siswa (*default*: `unpkediri`).
  - Pengaturan batas pelanggaran proctoring dan kode remedi per mapel.

---

## 🔑 Akun Bawaan (Default Seeder)

| Nama | Username | Nomor Induk / NISN | Password | Role |
| :--- | :--- | :--- | :--- | :--- |
| **Admin Guru** | `admin` | `198507152010011002` | `unpkediri` | **Guru / Admin** |
| **Zam Zam** | `zamzam` | `2024001` | `unpkediri` | **Guru / Admin** |
| **Yusuf** | `yusuf` | `2024002` | `unpkediri` | Siswa (Kelas 5) |
| **Yehosyua** | `yehosyua` | `2024003` | `unpkediri` | Siswa (Kelas 5) |
| **Cantika** | `cantika` | `2024004` | `unpkediri` | Siswa (Kelas 5) |

---

## 🗝️ Daftar Token Ujian / Kode Remedi

| Tingkat Kelas | Kode Remedi Default |
| :--- | :--- |
| **Kelas 1** | `REMEDI1` |
| **Kelas 2** | `REMEDI2` |
| **Kelas 3** | `REMEDI3` |
| **Kelas 4** | `REMEDI4` |
| **Kelas 5** | `REMEDI5` |
| **Kelas 6** | `REMEDI6` |
| **Kode Ganti Kelas Siswa** | `unpkediri` |

*(Kode ini dapat diubah sewaktu-waktu oleh Guru melalui Web Admin Dashboard).*

---

## 🚀 Panduan Menjalankan Sistem

### 1. Menjalankan Backend (Laravel)

Pastikan service **MySQL** sudah aktif (misal via XAMPP / Laragon / MySQL Service).

```bash
cd backend

# 1. Install dependencies
composer install

# 2. Siapkan file environment jika belum ada
cp .env.example .env

# 3. Generate key & JWT secret
php artisan key:generate
php artisan jwt:secret

# 4. Migrasi database dan seeder data awal
php artisan migrate:fresh --seed

# 5. Buat symlink storage untuk upload gambar/avatar
php artisan storage:link

# 6. Jalankan server lokal
php artisan serve --host=0.0.0.0 --port=8000
```

> **Akses Dashboard Pengawas:** Buka `http://localhost:8000/admin` pada browser.

---

### 2. Menjalankan Frontend (Android)

1. Buka folder `frontend` menggunakan **Android Studio**.
2. Pastikan perangkat Android atau Emulator berada di jaringan WiFi/LAN yang sama dengan PC server backend.
3. Bangun dan pasang APK:
   ```bash
   cd frontend
   ./gradlew assembleDebug
   ```
   *File APK siap pakai berlokasi di: `frontend/app/build/outputs/apk/debug/app-debug.apk`*
4. Jalankan aplikasi di emulator atau transfer APK ke perangkat tablet/HP Android siswa.

---

## 📁 Struktur Direktori

```text
CAT/
├── backend/                        # Laravel 10 Backend & Admin Web Portal
│   ├── app/
│   │   ├── Http/Controllers/       # AuthController, ExamController, AdminController, ProctoringController
│   │   └── Models/                 # User, Subject, Question, ExamResult, ProctoringLog
│   ├── database/                   # Migrations & DatabaseSeeder
│   ├── resources/views/admin/      # Blade View Admin Dashboard & Monitoring
│   └── routes/                     # api.php & web.php
│
├── frontend/                       # Android Jetpack Compose App
│   ├── app/src/main/java/com/tanilink/cat/
│   │   ├── data/                   # CatApiClient (Networking), ExamDatabaseHelper (SQLite)
│   │   ├── model/                  # Question, ExamSubject, ExamResult, UserAvatar
│   │   ├── proctoring/             # CameraProctoringView, FaceProctoringAnalyzer (ML Kit)
│   │   ├── ui/
│   │   │   ├── components/         # CatImage, Dialogs, Navbars
│   │   │   └── screens/            # Login, Home, Exam, Result, Review, History, Profile
│   │   ├── viewmodel/              # ExamViewModel (State Management)
│   │   └── MainActivity.kt         # Entry point & NavHost
│   └── build.gradle.kts
│
└── README.md
```

---

## 📄 Lisensi
Sistem aplikasi CAT SD dikembangkan untuk keperluan simulasi, asesmen, dan ujian berbasis komputer siswa Sekolah Dasar.
