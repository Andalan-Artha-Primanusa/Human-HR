<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Halaman kedaluwarsa</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8f5f1] text-slate-900">
  <main class="grid min-h-screen px-4 place-items-center">
    <section class="w-full max-w-lg overflow-hidden bg-white border shadow-sm rounded-2xl border-[#ead8c5]">
      <div class="h-1.5 bg-[#a77d52]"></div>
      <div class="p-8 text-center">
        <div class="grid w-14 h-14 mx-auto rounded-full place-items-center bg-amber-50 text-amber-700">
          <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"/>
          </svg>
        </div>
        <h1 class="mt-5 text-xl font-semibold">Sesi halaman sudah kedaluwarsa</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Refresh halaman, lalu coba simpan ulang. Kalau masih muncul, login ulang supaya token keamanan diperbarui.</p>
        <div class="flex flex-wrap justify-center gap-2 mt-6">
          <button type="button" onclick="window.location.reload()" class="px-4 py-2 text-sm font-semibold text-white rounded-lg bg-[#a77d52] hover:opacity-95">Refresh</button>
          <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold bg-white border rounded-lg border-slate-200 text-slate-700 hover:bg-slate-50">Login ulang</a>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
