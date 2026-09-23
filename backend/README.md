# CAT SD - Laravel Backend API

Backend API untuk **Aplikasi CAT (Computer Assisted Test) SD** dengan autentikasi JWT, MySQL, dan logging AI Proctoring.

---

## 🚀 Tech Stack
- **Framework**: Laravel 10.x
- **Database**: MySQL 8.x
- **Auth**: JWT (`tymon/jwt-auth`)
- **Proctoring**: Log violations & snapshots dari Android client

---

## 👤 Akun Awal (Default)

| Nama       | Username  | Password    |
|------------|-----------|-------------|
| Zam Zam    | zamzam    | unpkediri   |
| Yusuf      | yusuf     | unpkediri   |
| Yehosyua   | yehosyua  | unpkediri   |
| Cantika    | cantika   | unpkediri   |

---

## 📦 Instalasi

```bash
# 1. Clone dan masuk ke folder backend
cd backend/

# 2. Install dependencies
composer install

# 3. Copy .env dan atur konfigurasi
cp .env.example .env
# Edit .env: DB_DATABASE=cat_sd, DB_USERNAME, DB_PASSWORD

# 4. Generate App Key
php artisan key:generate

# 5. Generate JWT Secret
php artisan jwt:secret

# 6. Buat database MySQL
mysql -u root -p -e "CREATE DATABASE cat_sd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 7. Jalankan migration + seeder
php artisan migrate --seed

# 8. Jalankan server lokal
php artisan serve
```

Server akan berjalan di: `http://127.0.0.1:8000`

---

## 🔑 API Endpoints

### Auth
| Method | Endpoint        | Deskripsi                    |
|--------|-----------------|------------------------------|
| POST   | /api/auth/login | Login, mendapatkan JWT token |
| GET    | /api/auth/me    | Info user yang login         |
| POST   | /api/auth/logout| Logout, invalidate token     |

**Request Login:**
```json
{
  "username": "zamzam",
  "password": "unpkediri"
}
```

### Exam
| Method | Endpoint                              | Deskripsi                       |
|--------|---------------------------------------|---------------------------------|
| GET    | /api/subjects?grade=5                 | Daftar mata pelajaran per kelas |
| GET    | /api/subjects/{id}/questions          | 20 soal acak per mata pelajaran |
| POST   | /api/exam/submit                      | Submit hasil ujian              |
| GET    | /api/exam/history                     | Riwayat ujian user              |

### AI Proctoring
| Method | Endpoint                          | Deskripsi                      |
|--------|-----------------------------------|--------------------------------|
| POST   | /api/proctoring/log               | Log pelanggaran AI proctoring  |
| POST   | /api/proctoring/snapshot          | Upload foto snapshot kamera    |
| GET    | /api/proctoring/session/{examId}  | Log pelanggaran per sesi ujian |

---

## 🗄️ Database Schema

- `users` - Akun siswa
- `subjects` - Mata pelajaran per kelas
- `questions` - Soal per mata pelajaran
- `exam_results` - Hasil ujian siswa
- `proctoring_logs` - Log pelanggaran AI proctoring
- `proctoring_snapshots` - Foto snapshot kamera depan

---

## 🛡️ AI Proctoring Violation Types

| Type               | Keterangan                             |
|--------------------|----------------------------------------|
| `SWITCH_TAB`       | Pindah aplikasi/minimize saat ujian    |
| `NO_FACE`          | Wajah tidak terdeteksi di kamera       |
| `MULTIPLE_FACES`   | Lebih dari 1 wajah terdeteksi          |
| `LOOKING_AWAY`     | Pandangan tidak ke kamera              |
| `RAPID_ANSWERING`  | Jawaban terlalu cepat (< 2 detik)      |
| `SNAPSHOT_CAPTURED`| Snapshot acak berhasil diambil (log)   |
