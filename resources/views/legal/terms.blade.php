<!DOCTYPE html>
<html lang="id">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Terms & Conditions - Human.Careers</title>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
  <main class="mx-auto max-w-3xl px-4 py-10">
    <a href="{{ route('register') }}" class="text-sm font-semibold text-[#7a5531] underline">Kembali ke Register</a>

    <section class="mt-5 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
      <h1 class="text-2xl font-semibold text-slate-900">Terms & Conditions</h1>
      <p class="mt-2 text-sm text-slate-600">Terakhir diperbarui: {{ now()->format('d M Y') }}</p>

      <div class="mt-6 space-y-5 text-sm leading-6 text-slate-700">
        <div>
          <h2 class="font-semibold text-slate-900">1. Penggunaan Platform</h2>
          <p>Human.Careers digunakan untuk proses rekrutmen, pengelolaan profil kandidat, lamaran pekerjaan, psikotes, interview, dan komunikasi terkait seleksi.</p>
        </div>

        <div>
          <h2 class="font-semibold text-slate-900">2. Kebenaran Data</h2>
          <p>Pengguna wajib memberikan data yang benar, lengkap, dan terbaru. Data yang tidak valid dapat memengaruhi proses seleksi atau menyebabkan akun dibatasi.</p>
        </div>

        <div>
          <h2 class="font-semibold text-slate-900">3. Verifikasi Email</h2>
          <p>Setelah registrasi, pengguna wajib melakukan verifikasi email melalui link yang dikirim sistem sebelum mengakses fitur tertentu.</p>
        </div>

        <div>
          <h2 class="font-semibold text-slate-900">4. Keamanan Akun</h2>
          <p>Pengguna bertanggung jawab menjaga kerahasiaan password dan aktivitas yang terjadi melalui akunnya.</p>
        </div>

        <div>
          <h2 class="font-semibold text-slate-900">5. Penggunaan Data</h2>
          <p>Data pengguna digunakan untuk kebutuhan rekrutmen, evaluasi kandidat, komunikasi proses seleksi, dan administrasi internal sesuai kebijakan perusahaan.</p>
        </div>

        <div>
          <h2 class="font-semibold text-slate-900">6. Perubahan Ketentuan</h2>
          <p>Ketentuan dapat diperbarui sewaktu-waktu. Penggunaan platform setelah perubahan berlaku dianggap sebagai persetujuan terhadap ketentuan terbaru.</p>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
