# SOP Pelamar

Dokumen ini menjelaskan alur kerja pelamar di sistem Human-HR.

## 1. Tujuan
Membantu pelamar menggunakan sistem untuk registrasi, verifikasi email, melamar pekerjaan, mengikuti psikotes, melihat interview, dan mengelola notifikasi.

## 2. Prasyarat
- Memiliki akun aktif.
- Sudah login.
- Sudah menyelesaikan verifikasi email menggunakan OTP.

## 3. Ruang Lingkup
Fitur pelamar mencakup:
- Registrasi dan login
- Verifikasi email via OTP
- Melihat lowongan publik
- Melengkapi profil kandidat
- Melamar pekerjaan
- Melihat status lamaran
- Mengerjakan psikotes
- Melihat interview pribadi
- Download file ICS interview
- Mengelola notifikasi pribadi

## 4. Alur Kerja Pelamar

### 4.1 Registrasi
1. Buka halaman register.
2. Isi nama, email, dan password.
3. Sistem membuat akun dengan role pelamar.
4. Sistem mengirim kode OTP verifikasi email.

### 4.2 Login
1. Buka halaman login.
2. Masukkan email dan password.
3. Setelah login, jika email belum terverifikasi, sistem mengarahkan ke flow OTP.

### 4.3 Verifikasi Email OTP
1. Buka halaman input kode verifikasi.
2. Masukkan 6 digit kode yang dikirim ke email.
3. Jika kode benar dan belum kedaluwarsa, email ditandai verified.
4. Jika gagal, sistem menampilkan pesan error dan membatasi percobaan.

### 4.4 Melihat Lowongan
1. Buka daftar lowongan publik.
2. Gunakan filter seperti division, site, company, type, sort, dan term.
3. Buka detail lowongan untuk membaca deskripsi lengkap.

### 4.5 Melengkapi Profil
1. Buka form profil apply.
2. Lengkapi data diri dan data kandidat.
3. Simpan sebelum mengajukan lamaran.

### 4.6 Melamar Pekerjaan
1. Buka lowongan yang diinginkan.
2. Klik proses apply.
3. Submit lamaran.
4. Pantau status pada menu lamaran saya.

### 4.7 Psikotes
1. Buka halaman psikotes yang diberikan.
2. Isi jawaban seluruh pertanyaan.
3. Submit jawaban.
4. Sistem menghitung skor dan memindahkan stage bila lolos threshold.

### 4.8 Interview
1. Buka daftar interview milik sendiri.
2. Lihat detail jadwal interview.
3. Download file ICS jika ingin sinkron kalender.

### 4.9 Notifikasi
1. Buka daftar notifikasi.
2. Tandai satu notifikasi sebagai dibaca.
3. Tandai semua sebagai dibaca.
4. Hapus notifikasi yang tidak diperlukan.

## 5. Hak Akses Pelamar
Pelamar hanya boleh melihat dan mengubah data miliknya sendiri. Pelamar tidak boleh:
- Masuk ke admin panel
- Mengubah lowongan
- Mengubah data kandidat lain
- Melihat audit log
- Mengelola user

## 6. Validasi dan Security yang Relevan
- Role registrasi dipaksa menjadi pelamar.
- Semua fitur pribadi wajib melewati auth dan verified.
- Notifikasi dan interview dilindungi by ownership.
- Psikotes dilindungi agar hanya pemilik attempt yang bisa akses.

## 7. Kriteria Selesai
Pelamar dinyatakan berhasil memakai sistem bila:
- Sudah register dan login
- Sudah verifikasi email
- Berhasil apply pekerjaan
- Bisa memantau lamaran sendiri
- Bisa melihat interview dan notifikasi miliknya
