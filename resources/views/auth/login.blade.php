{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk • Human.Careers</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    [x-cloak] {
      display: none !important;
    }

    @keyframes shake {

      0%,
      100% {
        transform: translateX(0)
      }

      20% {
        transform: translateX(-4px)
      }

      40% {
        transform: translateX(4px)
      }

      60% {
        transform: translateX(-3px)
      }

      80% {
        transform: translateX(3px)
      }
    }

    .shake {
      animation: shake .35s ease-in-out 1;
    }
  </style>
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
    <div class="absolute top-0 left-0 z-10 items-center justify-center w-full h-full text-center bg-gradient-to-br from-[#a77d52]/90 to-[#8b5e3c] lg:items-center lg:justify-start lg:h-full lg:w-auto lg:bg-transparent lg:bg-gradient-to-r lg:from-[#a77d52] lg:via-[#b88a5c] lg:to-[#8b5e3c] lg:text-left">
      <div class="max-w-md p-8 lg:p-0 lg:pl-20">
        <h1 class="mb-4 text-3xl lg:text-4xl font-bold text-white">Human Careers</h1>
        <p class="text-white/80">
          Platform rekrutmen resmi Andalan Group untuk membantu Anda menemukan karier terbaik.
        </p>
      </div>
    </div>

    <!-- FORM -->
    <div class="absolute inset-0 z-10 flex items-center justify-center p-4 sm:p-6 lg:justify-end lg:pr-4 xl:pr-20">

      <div class="w-full max-w-md">

        @php
            $authError = $errors->first('email');
        @endphp

        @if ($authError)
            <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-rose-200 bg-rose-50 text-rose-800">
              {{ $authError }}
            </div>
        @endif

        <!-- CARD -->
        <div class="{{ $authError ? 'shake' : '' }}">
            <div class="p-8 border shadow-2xl rounded-2xl border-white/20 bg-[#d1d3c7] backdrop-blur">

            <!-- LOGO -->
            <div class="flex flex-col items-center mb-6 space-y-3">
              <img src="{{ asset('assets/logologin.png') }}"
                alt="Logo Perusahaan"
                class="object-contain h-28 md:h-32">
            </div>

            <!-- FORM -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
              @csrf

              <!-- EMAIL -->
              <div>
                <label class="text-sm text-gray-700">Email</label>
                <input type="email" name="email"
                  class="w-full mt-1 px-4 py-3 rounded-lg bg-[#f3e7d9] text-gray-800 placeholder-gray-500 border border-[#a77d52]/30 focus:ring-2 focus:ring-[#a77d52] focus:outline-none transition"
                  placeholder="nama@perusahaan.com">
              </div>

              <!-- PASSWORD -->
              <div>
                <label class="text-sm text-gray-700">Password</label>
                <input type="password" name="password"
                  class="w-full mt-1 px-4 py-3 rounded-lg bg-[#f3e7d9] text-gray-800 placeholder-gray-500 border border-[#a77d52]/30 focus:ring-2 focus:ring-[#a77d52] focus:outline-none transition"
                  placeholder="••••••••">
              </div>

              <!-- REMEMBER -->
              <div class="flex items-center justify-between text-sm text-gray-600">
                <label class="flex items-center gap-2">
                  <input type="checkbox" name="remember" class="rounded">
                  Ingat saya
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="hover:underline">
                      Lupa password?
                    </a>
                @endif
              </div>

              <!-- BUTTON -->
              <button type="submit"
                class="w-full bg-[#a77d52] text-white py-3 rounded-lg font-semibold hover:opacity-90 transition">
                Masuk
              </button>

            </form>

          </div>
        </div>

        <!-- REGISTER -->
        @if (Route::has('register'))
            <div class="mt-5 text-sm text-center text-white/80">
              Belum punya akun?
              <a href="{{ route('register') }}" class="underline">Daftar</a>
            </div>
        @endif

        <!-- FOOTER -->
        <div class="mt-6 text-xs text-center text-white/60">
          © {{ now()->year }} Andalan Group
        </div>

      </div>
    </div>

  </div>

</body>

</html>
