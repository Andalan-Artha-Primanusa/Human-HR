<!DOCTYPE html>
<html lang="id">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verifikasi Email - Human.Careers</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="min-h-screen text-slate-800">
  <div class="relative min-h-screen overflow-hidden">
    <img src="{{ asset('assets/hr1.jpg') }}" class="absolute inset-0 object-cover w-full h-full" alt="bg">
    <div class="absolute inset-0 bg-black/50"></div>

    <div class="absolute top-0 left-0 z-10 items-center hidden h-full pl-20 text-white lg:flex">
      <div class="max-w-md">
        <h1 class="mb-4 text-4xl font-bold">Human Careers</h1>
        <p class="text-white/80">
          Verifikasi email Anda untuk mulai menggunakan layanan rekrutmen Andalan Group.
        </p>
      </div>
    </div>

    <div class="absolute inset-0 z-10 flex items-center justify-end px-4 sm:pr-10 lg:pr-20">
      <div class="w-full max-w-md">
        @if (session('status') == 'verification-link-sent')
          <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-emerald-200 bg-emerald-50 text-emerald-800">
            Link verifikasi baru sudah dikirim ke email Anda.
          </div>
        @endif

        <div class="p-8 border shadow-2xl rounded-2xl border-white/20 bg-[#d1d3c7] backdrop-blur">
          <div class="flex flex-col items-center mb-6 space-y-3">
            <img src="{{ asset('assets/logologin.png') }}" alt="Logo Perusahaan" class="object-contain h-28 md:h-32">
          </div>

          <h1 class="mb-2 text-xl font-semibold text-gray-900">Cek email Anda</h1>
          <p class="text-sm leading-6 text-gray-700">
            Kami sudah mengirim link verifikasi ke
            <span class="font-semibold">{{ auth()->user()->email }}</span>.
            Klik link tersebut untuk mengaktifkan akun.
          </p>

          <div class="mt-6 space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
              @csrf
              <button type="submit" class="w-full bg-[#a77d52] text-white py-3 rounded-lg font-semibold hover:opacity-90 transition">
                Kirim Ulang Link Verifikasi
              </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="w-full rounded-lg border border-[#a77d52]/40 bg-white/60 py-3 font-semibold text-gray-800 hover:bg-white/80 transition">
                Keluar
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
