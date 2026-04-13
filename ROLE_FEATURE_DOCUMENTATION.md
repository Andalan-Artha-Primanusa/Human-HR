# Human-HR Role Feature Documentation

Dokumen ini menjelaskan fitur sistem berdasarkan peran utama:
- Pelamar
- HR
- Superadmin

Dokumen ini mengikuti implementasi route dan controller terkini.

## 1. Ringkasan Arsitektur Akses

### 1.1 Role
- pelamar: kandidat yang melamar lowongan
- hr: tim HR operasional
- superadmin: admin tertinggi sistem

### 1.2 Middleware utama
- auth: wajib login
- verified: wajib verifikasi email
- role:hr|superadmin: hanya HR dan Superadmin

### 1.3 Verifikasi email
Sistem menggunakan OTP (kode 6 digit), bukan link verifikasi default.
Flow utama:
1. User register
2. User login
3. User masuk ke halaman input OTP
4. Setelah OTP valid, status email_verified_at terisi
5. User baru bisa akses fitur dengan middleware verified

## 2. Matrix Fitur per Role

| Area Fitur | Pelamar | HR | Superadmin |
|---|---|---|---|
| Lihat lowongan publik | Ya | Ya | Ya |
| Detail lowongan publik (hanya open) | Ya | Ya | Ya |
| Isi profil pelamar | Ya | Tidak | Tidak |
| Melamar pekerjaan | Ya | Tidak | Tidak |
| Lihat lamaran sendiri | Ya | Tidak | Tidak |
| Kerjakan psikotes sendiri | Ya | Tidak | Tidak |
| Lihat interview sendiri | Ya | Tidak | Tidak |
| Download ICS interview sendiri | Ya | Tidak | Tidak |
| Kelola notifikasi sendiri | Ya | Ya | Ya |
| Dashboard manpower admin | Tidak | Ya | Ya |
| CRUD Jobs admin | Tidak | Ya | Ya |
| CRUD Sites admin | Tidak | Ya | Ya |
| CRUD Companies admin | Tidak | Ya | Ya |
| Board/Kanban aplikasi | Tidak | Ya | Ya |
| Kelola interview kandidat | Tidak | Ya | Ya |
| Kelola offer | Tidak | Ya | Ya |
| Lihat kandidat (admin) | Tidak | Ya | Ya |
| Kelola users admin | Tidak | Ya (terbatas) | Ya (penuh) |
| Lihat/export audit logs | Tidak | Ya | Ya |

Catatan:
- HR tidak boleh mengangkat user menjadi superadmin.
- Hanya superadmin yang boleh assign role superadmin.

## 3. Flow Pelamar

### 3.1 Registrasi dan login
1. Buka halaman register
2. Isi nama, email, password
3. Sistem membuat user dengan role pelamar
4. Sistem kirim OTP verifikasi email
5. User login
6. User input OTP
7. Jika sukses, user dianggap verified

### 3.2 Eksplor lowongan
1. Buka daftar lowongan publik
2. Filter berdasarkan division, site, company, type, term, sort
3. Buka detail lowongan

### 3.3 Apply lowongan
1. Buka form profil apply
2. Lengkapi data kandidat
3. Submit lamaran ke job terkait
4. Pantau status di menu lamaran saya

### 3.4 Psikotes
1. Jika stage mengharuskan psikotes, pelamar membuka halaman tes
2. Isi jawaban
3. Submit
4. Sistem hitung score
5. Jika lulus threshold, stage aplikasi bergerak ke tahap berikutnya

### 3.5 Interview pribadi
1. Pelamar melihat daftar interview miliknya
2. Pelamar melihat detail interview
3. Pelamar dapat download file ICS untuk sinkron kalender

### 3.6 Notifikasi
1. Lihat daftar notifikasi
2. Tandai satu notifikasi dibaca
3. Tandai semua notifikasi dibaca
4. Hapus notifikasi tertentu

## 4. Flow HR

### 4.1 Akses admin
Syarat:
- login
- email verified
- role hr atau superadmin

### 4.2 Manpower planning
1. Buka admin manpower
2. Cari job
3. Ubah kebutuhan headcount per job
4. Sync kebutuhan dengan openings lowongan
5. Pantau ringkasan di dashboard manpower

### 4.3 Job management
1. Buat lowongan
2. Edit lowongan
3. Tutup atau hapus lowongan sesuai kebijakan
4. Gunakan filter pencarian dan company/site mapping

### 4.4 Site dan company management
1. CRUD Site
2. CRUD Company
3. Pastikan data master valid agar proses rekrutmen konsisten

### 4.5 Pipeline kandidat
1. Buka board aplikasi
2. Pindah stage kandidat
3. Jadwalkan interview
4. Buat offer
5. Monitor psikotes dan hasil stage

### 4.6 Kandidat dan dokumen
1. Lihat daftar kandidat
2. Buka detail kandidat
3. Akses CV kandidat dari panel admin

### 4.7 User management (batas HR)
1. Buat atau edit user non-superadmin
2. Tidak boleh set role superadmin
3. Tidak boleh mengubah akun superadmin

### 4.8 Audit logs
1. Buka daftar audit log
2. Filter berdasarkan event, user, target, date range
3. Lihat detail log
4. Export CSV untuk keperluan audit

## 5. Flow Superadmin

Superadmin memiliki semua kemampuan HR ditambah kontrol role tertinggi.

Tambahan otoritas penting:
1. Boleh assign role superadmin
2. Boleh memodifikasi akun superadmin lain sesuai kebijakan internal
3. Menjadi owner akhir untuk governance akses user

## 6. Security Guardrails yang Sudah Diterapkan

1. Registrasi publik dipaksa role pelamar
2. Endpoint admin dilindungi middleware auth + verified + role
3. Akses data user dibatasi per ownership atau role
4. Detail lowongan publik tetap mengikuti policy view
5. Verifikasi email menggunakan OTP dengan throttle
6. Notifikasi user hanya bisa diakses pemilik notifikasi
7. Ekspor data besar memakai streaming/chunk untuk stabilitas memori
8. Input pencarian disanitasi untuk menekan query liar
9. Role escalation ke superadmin dibatasi untuk superadmin saja

## 7. Rute Web Penting

### 7.1 Public
- GET /jobs
- GET /jobs/{job}
- GET /sites
- GET /sites/{site}

### 7.2 Auth + OTP
- GET /email/verify
- GET /email/verify/code
- POST /email/verify/code
- POST /email/verify/resend

### 7.3 Pelamar (verified)
- GET /me/applications
- GET /me/psychotest/{attempt}
- POST /me/psychotest/{attempt}
- GET /me/interviews
- GET /me/interviews/{interview}
- GET /me/interviews/{interview}/ics
- GET /me/notifications
- POST /me/notifications/read-all
- POST /me/notifications/{notification}/read
- DELETE /me/notifications/{notification}

### 7.4 Admin (HR/Superadmin)
Prefix: /admin
- manpower
- dashboard/manpower
- jobs
- sites
- companies
- applications + board
- interviews
- psychotests
- offers
- candidates
- users + import/export
- audit-logs + export

## 8. Rute API Penting

### 8.1 Auth API
- POST /api/login

### 8.2 API private (token + verified)
- GET /api/me
- GET /api/users (HR/Superadmin)
- GET /api/users/{user} (HR/Superadmin)

### 8.3 API public
- GET /api/public/users
- GET /api/public/users/{user}

## 9. Checklist UAT per Role

### 9.1 Pelamar
1. Register -> OTP -> verified sukses
2. Apply job sukses
3. Lihat lamaran sendiri sukses
4. Akses psikotes milik sendiri saja
5. Akses interview dan ICS milik sendiri saja

### 9.2 HR
1. Login verified ke /admin sukses
2. CRUD job, site, company berjalan
3. Move stage kandidat berjalan
4. Buat interview dan offer berjalan
5. Tidak bisa assign superadmin

### 9.3 Superadmin
1. Semua fitur HR berjalan
2. Dapat assign role superadmin
3. Dapat mengelola user superadmin
4. Audit log dan export berjalan normal

## 10. Catatan Operasional

1. Saat migrasi dari flow verifikasi email link ke OTP, beberapa test bawaan Breeze perlu disesuaikan.
2. Gunakan seed role dan stage secara konsisten sebelum UAT.
3. Untuk keamanan produksi, pastikan rate limit dan mail delivery dipantau.
4. Untuk performa, pastikan index DB pada kolom filter yang sering dipakai (status, created_at, user_id, role).
