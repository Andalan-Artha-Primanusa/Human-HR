# SOP Superadmin

Dokumen ini menjelaskan alur kerja dan kewenangan Superadmin di sistem Human-HR.

## 1. Tujuan
Menjadi acuan otorisasi tertinggi untuk pengelolaan sistem, user, dan governance akses.

## 2. Prasyarat
- Login dengan role superadmin.
- Email sudah terverifikasi.
- Memahami bahwa semua tindakan superadmin bersifat sensitif dan tercatat di audit log.

## 3. Ruang Lingkup
Superadmin memiliki semua kewenangan HR, ditambah:
- Mengelola role tertinggi
- Mengubah user superadmin lain
- Mengawasi audit log dan keamanan sistem
- Menjadi pihak terakhir untuk keputusan akses

## 4. Alur Kerja Superadmin

### 4.1 Masuk ke Sistem
1. Login dengan akun superadmin.
2. Pastikan verified.
3. Masuk ke `/admin`.
4. Verifikasi akses panel berhasil.

### 4.2 Operasional Rekrutmen
Superadmin dapat melakukan seluruh tugas HR:
- Dashboard manpower
- Lowongan
- Site dan company
- Board aplikasi
- Interview
- Offer
- Kandidat
- User management
- Audit log

### 4.3 Governance User
1. Buka menu users.
2. Buat atau edit user.
3. Tetapkan role sesuai kebutuhan bisnis.
4. Jika perlu assign superadmin, hanya superadmin yang boleh melakukannya.
5. Perubahan akun superadmin lain harus dilakukan dengan sangat hati-hati.

### 4.4 Audit dan Investigasi
1. Buka audit log.
2. Filter aksi berdasarkan event, aktor, target, dan tanggal.
3. Gunakan detail log untuk investigasi perubahan data.
4. Export CSV bila diperlukan untuk dokumentasi formal.

### 4.5 Kontrol Risiko
1. Tinjau akun yang memiliki akses tinggi.
2. Pastikan role escalation hanya untuk kebutuhan resmi.
3. Pastikan akun yang tidak aktif dicabut aksesnya.
4. Pantau aktivitas login, perubahan data kritikal, dan export data.

## 5. Hak Akses Superadmin
Superadmin boleh:
- Mengelola semua fitur HR
- Mengelola superadmin lain
- Menyetujui perubahan akses tertinggi
- Melihat audit log dan melakukan investigasi

Superadmin tidak boleh:
- Mengabaikan audit trail
- Memberi akses superadmin tanpa kebutuhan operasional yang jelas
- Menjadikan data kandidat di luar konteks rekrutmen

## 6. Security dan Akuntabilitas
- Semua perubahan sensitif masuk audit log.
- Role superadmin tidak boleh dipasang dari registrasi publik.
- HR tidak boleh mengangkat superadmin.
- Endpoint admin tetap membutuhkan verified dan role guard.

## 7. Kriteria Selesai
Proses superadmin dianggap benar bila:
- Semua perubahan role dilakukan sengaja dan tercatat
- Audit log bisa dipakai untuk jejak perubahan
- Akses tertinggi tetap terkontrol
- Tidak ada privilege escalation dari jalur publik
