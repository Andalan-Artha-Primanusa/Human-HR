<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Halaman tidak ditemukan</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8f5f1] text-slate-900">
  <main class="grid min-h-screen px-4 place-items-center">
    <section class="w-full max-w-lg overflow-hidden bg-white border shadow-sm rounded-2xl border-[#ead8c5]">
      <div class="h-1.5 bg-[#a77d52]"></div>
      <div class="p-8 text-center">
        <h1 class="text-xl font-semibold">Halaman tidak ditemukan</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Link yang dibuka sudah tidak tersedia atau alamatnya kurang tepat.</p>
        <div class="flex flex-wrap justify-center gap-2 mt-6">
          <button type="button" onclick="history.back()" class="px-4 py-2 text-sm font-semibold text-white rounded-lg bg-[#a77d52] hover:opacity-95">Kembali</button>
          <a href="{{ route('jobs.index') }}" class="px-4 py-2 text-sm font-semibold bg-white border rounded-lg border-slate-200 text-slate-700 hover:bg-slate-50">Cari Lowongan</a>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
