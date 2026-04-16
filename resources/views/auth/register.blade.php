{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Akun • Human.Careers</title>
</head>

<body class="min-h-screen text-slate-800">

<div class="relative min-h-screen overflow-hidden">

  <!-- BACKGROUND -->
  <img src="{{ asset('assets/hr1.jpg') }}"
       class="absolute inset-0 object-cover w-full h-full"
       alt="bg">

  <!-- overlay -->
  <div class="absolute inset-0 bg-black/50"></div>

  <!-- TEXT KIRI -->
  <div class="absolute top-0 left-0 z-10 items-center hidden h-full pl-20 text-white lg:flex">
    <div class="max-w-md">
      <h1 class="mb-3 text-4xl font-bold">Human Careers</h1>
      <p class="text-sm text-white/80">
        Platform rekrutmen resmi Andalan Group untuk membantu Anda menemukan karier terbaik.
      </p>
    </div>
  </div>

  <!-- FORM -->
  <div class="absolute inset-0 z-10 flex items-center justify-end pr-6 sm:pr-14 lg:pr-28">

    <div class="w-full max-w-sm">

      <!-- CARD -->
      <div class="rounded-2xl shadow-2xl p-8 border border-white/20 bg-[#d1d3c7] backdrop-blur max-h-[90vh] overflow-y-auto">

        <!-- LOGO (DIPERBESAR) -->
        <div class="flex flex-col items-center mb-6 space-y-3">
          <img src="{{ asset('assets/logologin.png') }}"
            alt="Logo Perusahaan"
            class="object-contain h-28 md:h-32">
        </div>

        <!-- FORM -->
        <form method="POST" action="{{ route('register') }}" class="space-y-3">
          @csrf

          <div>
            <label class="text-sm text-gray-700">Nama Lengkap</label>
            <input type="text" name="name"
              class="w-full mt-1 px-3 py-2 rounded-lg bg-[#f3e7d9] text-gray-800 placeholder-gray-500 border border-[#a77d52]/30 focus:ring-2 focus:ring-[#a77d52] focus:outline-none"
              placeholder="Nama sesuai KTP">
          </div>

          <div>
            <label class="text-sm text-gray-700">Email</label>
            <input type="email" name="email"
              class="w-full mt-1 px-3 py-2 rounded-lg bg-[#f3e7d9] text-gray-800 placeholder-gray-500 border border-[#a77d52]/30 focus:ring-2 focus:ring-[#a77d52] focus:outline-none"
              placeholder="nama@email.com">
          </div>

          <div>
            <label class="text-sm text-gray-700">Password</label>
            <input type="password" name="password"
              class="w-full mt-1 px-3 py-2 rounded-lg bg-[#f3e7d9] text-gray-800 border border-[#a77d52]/30 focus:ring-2 focus:ring-[#a77d52] focus:outline-none">
          </div>

          <div>
            <label class="text-sm text-gray-700">Konfirmasi Password</label>
            <input type="password" name="password_confirmation"
              class="w-full mt-1 px-3 py-2 rounded-lg bg-[#f3e7d9] text-gray-800 border border-[#a77d52]/30 focus:ring-2 focus:ring-[#a77d52] focus:outline-none">
          </div>

          <div class="flex items-start gap-2 text-xs text-gray-600">
            <input type="checkbox" class="mt-1 rounded">
            <label>Saya menyetujui Ketentuan & Privasi</label>
          </div>

          <button type="submit"
            class="w-full bg-[#a77d52] text-white py-2 rounded-lg font-semibold hover:opacity-90 transition">
            Buat Akun
          </button>

        </form>

      </div>

      <!-- LOGIN -->
      <div class="mt-4 text-sm text-center text-white/80">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="underline">Masuk</a>
      </div>

    </div>
  </div>

</div>

</body>
</html>