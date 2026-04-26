<!DOCTYPE html>
<html lang="id">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Akun - Human.Careers</title>
</head>

<body class="min-h-screen text-slate-800">

<div class="relative min-h-screen overflow-hidden">

  <img src="{{ asset('assets/hr1.jpg') }}" class="absolute inset-0 object-cover w-full h-full" alt="bg">
  <div class="absolute inset-0 bg-black/50"></div>

  <div class="absolute top-0 left-0 z-10 items-center justify-center w-full h-full text-center bg-gradient-to-br from-[#a77d52]/90 to-[#8b5e3c] lg:items-center lg:justify-start lg:h-full lg:w-auto lg:bg-transparent lg:bg-gradient-to-r lg:from-[#a77d52] lg:via-[#b88a5c] lg:to-[#8b5e3c] lg:text-left">
    <div class="max-w-md p-8 lg:p-0 lg:pl-20">
      <h1 class="mb-3 text-3xl lg:text-4xl font-bold text-white">Human Careers</h1>
      <p class="text-sm text-white/80">Platform rekrutmen resmi Andalan Group.</p>
    </div>
  </div>

  <div class="absolute inset-0 z-10 flex items-center justify-center p-4 sm:p-6 lg:justify-end lg:pr-6 xl:pr-28">
    <div class="w-full max-w-sm">
      <div class="rounded-2xl shadow-2xl p-8 border border-white/20 bg-[#d1d3c7] backdrop-blur max-h-[90vh] overflow-y-auto">
        <div class="flex flex-col items-center mb-6 space-y-3">
          <img src="{{ asset('assets/logologin.png') }}" alt="Logo" class="object-contain h-28 md:h-32">
        </div>

        @if ($errors->any())
          <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            <div class="font-semibold">Register belum berhasil</div>
            <ul class="mt-1 list-disc space-y-1 pl-4">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-3">
          @csrf

          <div>
            <label class="text-sm text-gray-700">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-[#f3e7d9] border" placeholder="Nama" required>
            @error('name')
              <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label class="text-sm text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-[#f3e7d9] border" placeholder="email@email.com" required>
            @error('email')
              <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label class="text-sm text-gray-700">Password</label>
            <input type="password" name="password" class="w-full mt-1 px-3 py-2 rounded-lg bg-[#f3e7d9] border" minlength="6" required>
            @error('password')
              <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label class="text-sm text-gray-700">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="w-full mt-1 px-3 py-2 rounded-lg bg-[#f3e7d9] border" minlength="6" required>
          </div>

          <div class="rounded-lg border border-[#a77d52]/30 bg-white/35 px-3 py-2">
            <label for="agree" class="flex items-start gap-2 text-xs leading-5 text-gray-700">
              <input id="agree" type="checkbox" name="agree" value="1" class="mt-1 rounded border-gray-300" {{ old('agree') ? 'checked' : '' }} required>
              <span>
                Saya sudah membaca dan menyetujui
                <a href="{{ route('terms') }}" target="_blank" rel="noopener" class="font-semibold text-[#7a5531] underline">
                  Terms & Conditions
                </a>
                Human.Careers.
              </span>
            </label>
            @error('agree')
              <div class="mt-1 text-xs text-red-700">{{ $message }}</div>
            @enderror
          </div>

          <button id="registerSubmit" type="submit" class="w-full bg-[#a77d52] text-white py-2 rounded-lg font-semibold disabled:cursor-not-allowed disabled:opacity-50" disabled>Buat Akun</button>
        </form>

      </div>
      <div class="mt-4 text-sm text-center text-white/80">
        Sudah punya akun? <a href="/login" class="underline">Masuk</a>
      </div>
    </div>
  </div>

</div>

<script>
  (function () {
    const agree = document.getElementById('agree');
    const submit = document.getElementById('registerSubmit');

    function syncSubmit() {
      if (submit) {
        submit.disabled = !agree?.checked;
      }
    }

    agree?.addEventListener('change', syncSubmit);
    syncSubmit();
  })();
</script>
</body>
</html>
