<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Terjadi gangguan</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8f5f1] text-slate-900">
  <main class="grid min-h-screen px-4 place-items-center">
    <section class="w-full max-w-lg overflow-hidden bg-white border shadow-sm rounded-2xl border-[#ead8c5]">
      <div class="h-1.5 bg-red-500"></div>
      <div class="p-8 text-center">
        <div class="grid w-14 h-14 mx-auto text-red-700 rounded-full place-items-center bg-red-50">
          <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
          </svg>
        </div>
        <h1 class="mt-5 text-xl font-semibold">Sistem sedang mengalami gangguan</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Permintaan belum bisa diproses. Coba ulangi beberapa saat lagi atau kembali ke dashboard.</p>
        <div class="flex flex-wrap justify-center gap-2 mt-6">
          <button type="button" onclick="history.back()" class="px-4 py-2 text-sm font-semibold text-white rounded-lg bg-[#a77d52] hover:opacity-95">Kembali</button>
          <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-semibold bg-white border rounded-lg border-slate-200 text-slate-700 hover:bg-slate-50">Dashboard</a>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
