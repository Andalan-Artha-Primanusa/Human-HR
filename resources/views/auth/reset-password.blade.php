{{-- resources/views/auth/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Atur Ulang Password • Human.Careers</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- === Vite build tanpa @vite (baca manifest.json) === --}}

  <style>
    .bg-dots{
      background-image:
        radial-gradient(#e5e7eb 1px, transparent 1px),
        radial-gradient(#e5e7eb 1px, transparent 1px);
      background-position: 0 0, 10px 10px;
      background-size: 20px 20px;
    }
  </style>
</head>
<body class="min-h-screen bg-white text-slate-800">
  <div class="grid min-h-screen grid-cols-1 lg:grid-cols-2">

    {{-- Kiri: foto --}}
    <div class="relative hidden lg:block">
      <img src="{{ asset('assets/hr1.jpg') }}" alt="Aktivitas tim Andalan"
           class="absolute inset-0 object-cover w-full h-full" loading="lazy" referrerpolicy="no-referrer">
    </div>

    {{-- Kanan: form --}}
    <div class="relative flex items-center justify-center min-h-screen p-6 overflow-hidden bg-dots sm:p-10">
      <div class="w-full max-w-md">

        {{-- Brand --}}
        <div class="mb-8 text-center">
          <div class="inline-flex items-center gap-2 px-3 py-1 mt-3 text-xs font-medium border rounded-full shadow-sm border-slate-200 bg-white/70 backdrop-blur">
            Atur Ulang Kata Sandi
          </div>
        </div>

        {{-- Alert global (error throttle/umum) --}}
        @if ($errors->any())
              <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-rose-200 bg-rose-50 text-rose-800">
                {{ $errors->first() }}
              </div>
        @endif

        <div class="p-6 shadow-xl rounded-2xl bg-white/80 ring-1 ring-slate-200/80 backdrop-blur">
          {{-- 🔧 GANTI ke password.store --}}
          <form id="resetForm" method="POST" action="{{ route('password.store') }}" class="space-y-5" novalidate>
            @csrf

            {{-- Token --}}
            <input type="hidden" name="token" value="{{ request('token') }}">

            {{-- Email --}}
            <div>
              <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
              <input id="email" type="email" name="email"
                     value="{{ old('email', request('email')) }}"
                     required inputmode="email" autocapitalize="none" autocomplete="username" spellcheck="false"
                     class="block w-full mt-1 rounded-lg shadow-sm border-slate-300 focus:border-blue-600 focus:ring-blue-600">
              @error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div>
              <label for="password" class="block text-sm font-medium text-slate-700">Password Baru</label>
              <input id="password" type="password" name="password" required autocomplete="new-password"
                     class="block w-full mt-1 rounded-lg shadow-sm border-slate-300 focus:border-blue-600 focus:ring-blue-600">
              @error('password') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
              <p class="mt-1 text-xs text-slate-500">Minimal sesuai kebijakan keamanan (huruf, angka, dst.).</p>
            </div>

            {{-- Konfirmasi Password --}}
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
              <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                     class="block w-full mt-1 rounded-lg shadow-sm border-slate-300 focus:border-blue-600 focus:ring-blue-600">
              @error('password_confirmation') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <button id="submitBtn" type="submit"
              class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed">
              <svg id="spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
              </svg>
              <span>Reset Password</span>
            </button>

            {{-- Link balik --}}
            <div class="text-sm text-center text-slate-600">
              Ingat kata sandi? @if (Route::has('login'))
                <a class="font-medium text-blue-700 underline hover:text-blue-800" href="{{ route('login') }}">Masuk</a>
              @endif
            </div>
          </form>
        </div>

        {{-- Footer --}}
        <div class="mt-6 text-xs text-center text-slate-500">
          © {{ now()->year }} Andalan Group • Human.Careers
        </div>
      </div>
    </div>
  </div>

  {{-- UX: cegah double submit --}}
  <script>
    (function () {
      const form = document.getElementById('resetForm');
      const btn = document.getElementById('submitBtn');
      const spinner = document.getElementById('spinner');
      const email = document.getElementById('email');
      if (!email.value) email.focus();

      form?.addEventListener('submit', function () {
        btn?.setAttribute('disabled','disabled');
        spinner?.classList.remove('hidden');
      }, { once: true });
    })();
  </script>
</body>
</html>
