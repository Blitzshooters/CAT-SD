# CAT SD - Laravel Backend API & Admin Web Portal

Backend RESTful API dan Portal Manajemen Ujian untuk **Aplikasi CAT (Computer Assisted Test) SD** dengan autentikasi JWT, database MySQL, dan pemantauan AI Proctoring real-time.

---

## 🚀 Tech Stack
- **Framework**: Laravel 10.x (PHP 8.2+)
- **Database**: MySQL 8.x
- **Authentication**: JWT (`tymon/jwt-auth`)
- **Frontend Admin**: TailwindCSS, Alpine.js, FontAwesome 6
- **Proctoring**: Real-time violation logging dari Android client

---

## 👤 Akun Bawaan (Default Seeder)

| Nama        | Username  | Nomor Induk / NISN   | Password    | Role          |
|-------------|-----------|----------------------|-------------|---------------|
| Admin Guru  | admin     | 198507152010011002   | unpkediri   | Guru / Admin  |
| Zam Zam     | zamzam    | 2024001              | unpkediri   | Guru / Admin  |
| Yusuf       | yusuf     | 2024002              | unpkediri   | Siswa (Kls 5) |
| Yehosyua    | yehosyua  | 2024003              | unpkediri   | Siswa (Kls 5) |
| Cantika     | cantika   | 2024004              | unpkediri   | Siswa (Kls 5) |

---

## 📦 Panduan Instalasi

```bash
# 1. Masuk ke direktori backend
cd backend/

# 2. Install dependencies Composer
composer install

# 3. Salin file environment
cp .env.example .env

# 4. Generate App Key & JWT Secret
php artisan key:generate
php artisan jwt:secret

# 5. Buat database MySQL
mysql -u root -p -e "CREATE DATABASE cat_sd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Jalankan migrasi dan seeder bank soal
php artisan migrate --seed

# 7. Hubungkan symlink storage publik
php artisan storage:link

# 8. Jalankan server lokal
php artisan serve --host=0.0.0.0 --port=8000
```

> **Akses Dashboard Web Pengawas:** Buka `http://localhost:8000/admin` pada browser.

---

## 🔑 API Endpoints

### 1. Autentikasi (`/api/auth`)
| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/auth/login` | Login siswa/guru via Username atau Nomor Induk |
| `GET` | `/api/auth/me` | Informasi profil user yang sedang login (Bearer token) |
| `POST` | `/api/auth/logout` | Invalidate JWT token |
| `POST` | `/api/auth/refresh` | Refresh JWT token yang aktif |

### 2. Ujian & Bank Soal (`/api/exam`)
| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/subjects?grade={1-6}` | Daftar mata pelajaran per jenjang kelas |
| `GET` | `/api/subjects/{id}/questions` | Butir soal lengkap (gambar, opsi, penjelasan) |
| `POST` | `/api/exam/verify-remedy` | Verifikasi kode remedi untuk reset pelanggaran ujian |
| `POST` | `/api/exam/submit` | Simpan hasil ujian siswa ke database |
| `GET` | `/api/exam/history` | Riwayat hasil ujian siswa |
| `GET` | `/api/class-code` | Ambil kode izin ganti kelas |
| `POST` | `/api/class-code/verify` | Verifikasi kode izin ganti kelas siswa |

### 3. AI Proctoring (`/api/proctoring`)
| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/proctoring/log` | Catat pelanggaran pengawasan dari Android client |
| `GET` | `/api/proctoring/session/{examId}` | Ambil catatan log pelanggaran per sesi ujian |

### 4. Profil Pengguna (`/api/user`)
| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/user/profile` | Perbarui nama, tingkat kelas, atau avatar |
| `POST` | `/api/user/upload-avatar` | Unggah foto profil kustom (Base64) |
| `POST` | `/api/user/change-password` | Ubah password akun siswa/guru |

---

## 🗄️ Database Schema

- `users` — Akun siswa dan guru (username, nomor_induk, password, avatar, is_admin)
- `subjects` — Mata pelajaran per jenjang kelas (title, grade, duration_minutes, max_violations, remedy_code)
- `questions` — Bank soal ujian (prompt, question_image, options, option_images, correct_answer_index, explanation)
- `exam_results` — Hasil ujian siswa (skor, benar, salah, kosong, durasi pengerjaan)
- `proctoring_logs` — Catatan pelanggaran AI proctoring
- `app_settings` — Konfigurasi global sistem (kode penggantian kelas, dll.)

---

## 🛡️ Tipe Pelanggaran AI Proctoring

| Type | Keterangan |
|---|---|
| `SWITCH_TAB` | Siswa beralih aplikasi / keluar layar ujian |
| `NO_FACE` | Wajah siswa tidak terdeteksi oleh kamera AI |
| `MULTIPLE_FACES` | Terdeteksi lebih dari 1 orang di depan kamera |
| `LOOKING_AWAY` | Siswa menoleh / pandangan berpaling dari layar |
| `RAPID_ANSWERING` | Siswa menjawab terlalu kilat (< 2 detik) |

---

## 🗝️ Token Remedi per Kelas

* **Kelas 1**: `REMEDI1`
* **Kelas 2**: `REMEDI2`
* **Kelas 3**: `REMEDI3`
* **Kelas 4**: `REMEDI4`
* **Kelas 5**: `REMEDI5`
* **Kelas 6**: `REMEDI6`
* **Kode Ganti Kelas**: `unpkediri`
