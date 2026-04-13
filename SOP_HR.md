# SOP HR

Dokumen ini menjelaskan alur kerja HR di sistem Human-HR.

## 1. Tujuan
Membantu tim HR mengelola rekrutmen, manpower, lowongan, interview, offer, kandidat, dan audit aktivitas.

## 2. Prasyarat
- Login dengan akun role hr atau superadmin.
- Email sudah terverifikasi.
- Akses admin panel aktif.

## 3. Ruang Lingkup
Fitur HR mencakup:
- Dashboard manpower
- CRUD lowongan
- CRUD site dan company
- Pengelolaan manpower requirement
- Board aplikasi kandidat
- Penjadwalan interview
- Pembuatan offer
- Monitoring psikotes
- Lihat kandidat
- Kelola user non-superadmin
- Audit log

## 4. Alur Kerja HR

### 4.1 Masuk ke Admin Panel
1. Login.
2. Pastikan email sudah verified.
3. Masuk ke `/admin`.
4. Akses dibatasi hanya untuk HR dan Superadmin.

### 4.2 Manpower Dashboard
1. Buka dashboard manpower.
2. Lihat jumlah lowongan open.
3. Lihat active applications.
4. Lihat distribusi stage.
5. Lihat fulfillment kebutuhan manpower.

### 4.3 Manpower Requirement
1. Buka menu manpower.
2. Cari job yang ingin dikelola.
3. Edit kebutuhan headcount.
4. Simpan perubahan.
5. Sistem menyinkronkan openings lowongan.

### 4.4 Kelola Lowongan
1. Buat lowongan baru.
2. Isi code, title, division, level, employment type, status, site, dan company.
3. Tambahkan skills dan keywords bila diperlukan.
4. Simpan.
5. Saat update, pastikan site dan company valid.

### 4.5 Kelola Site dan Company
1. Tambah site baru bila ada lokasi kerja baru.
2. Update data region, timezone, address, dan meta bila perlu.
3. Tambah atau ubah company sesuai struktur organisasi.
4. Hindari penghapusan jika masih dipakai job aktif.

### 4.6 Board Aplikasi Kandidat
1. Buka applications board.
2. Lihat kandidat per stage.
3. Pindahkan kandidat ke stage berikutnya sesuai hasil seleksi.
4. Tambahkan catatan jika diperlukan.

### 4.7 Interview
1. Pilih kandidat yang lolos ke tahap interview.
2. Buat jadwal interview.
3. Isi mode, lokasi, meeting link, start_at, end_at, dan catatan.
4. Simpan.
5. Kandidat akan melihat interview di menu pribadinya.

### 4.8 Psychotest
1. Pantau attempt psikotes dari admin.
2. Cari berdasarkan kandidat atau job.
3. Tinjau status active atau finished.
4. Gunakan hasil sebagai dasar perpindahan stage.

### 4.9 Offer
1. Buat draft offer untuk kandidat yang lolos.
2. Isi komponen salary dan detail lain sesuai kebijakan.
3. Generate PDF bila diperlukan.
4. Kirim atau lanjutkan ke tahap tanda tangan.

### 4.10 Candidate Management
1. Buka daftar kandidat.
2. Lihat profile dan CV.
3. Gunakan data kandidat untuk evaluasi seleksi.

### 4.11 User Management
1. Buat user non-superadmin bila diperlukan.
2. Ubah role sesuai kewenangan.
3. Jangan menetapkan role superadmin.
4. Jangan mengubah akun superadmin bila bukan superadmin.

### 4.12 Audit Log
1. Buka daftar audit log.
2. Filter berdasarkan event, user, target, dan range tanggal.
3. Buka detail log jika perlu investigasi.
4. Export CSV untuk dokumentasi audit.

## 5. Hak Akses HR
HR boleh mengelola operasional rekrutmen, tetapi tidak boleh:
- Mengangkat user menjadi superadmin
- Mengubah akun superadmin
- Keluar dari scope admin panel
- Mengakses data yang bukan haknya jika dibatasi kebijakan site/company

## 6. Security dan Kontrol
- Semua route admin memakai auth, verified, dan role guard.
- Input query dibatasi dan disanitasi.
- Export audit memakai streaming agar aman untuk data besar.
- Pencarian job dan site memakai whitelist filter.
- Perubahan role superadmin dibatasi untuk superadmin.

## 7. Kriteria Selesai
Proses HR dianggap benar bila:
- Lowongan terdata dan sesuai manpower
- Kandidat bergerak mengikuti stage yang benar
- Interview dan offer tercatat
- Audit log dapat ditelusuri
- Tidak ada eskalasi role yang tidak sah
