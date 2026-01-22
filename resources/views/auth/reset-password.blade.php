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
  <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

    {{-- Kiri: foto --}}
    <div class="relative hidden lg:block">
      <img src="{{ asset('assets/hr1.jpg') }}" alt="Aktivitas tim Andalan"
           class="absolute inset-0 h-full w-full object-cover" loading="lazy" referrerpolicy="no-referrer">
    </div>

    {{-- Kanan: form --}}
    <div class="bg-dots flex min-h-screen items-center justify-center p-6 sm:p-10 relative overflow-hidden">
      <div class="w-full max-w-md">

        {{-- Brand --}}
        <div class="mb-8 text-center">
          <img src="{{ asset('assets/foto2.png') }}" alt="Andalan" class="mx-auto h-20 sm:h-24 md:h-28 w-auto">
          <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-1 text-xs font-medium shadow-sm backdrop-blur">
            Atur Ulang Kata Sandi
          </div>
        </div>

        {{-- Alert global (error throttle/umum) --}}
        @if ($errors->any())
          <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
            {{ $errors->first() }}
          </div>
        @endif

        <div class="rounded-2xl bg-white/80 p-6 shadow-xl ring-1 ring-slate-200/80 backdrop-blur">
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
                     class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">
              @error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div>
              <label for="password" class="block text-sm font-medium text-slate-700">Password Baru</label>
              <input id="password" type="password" name="password" required autocomplete="new-password"
                     class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">
              @error('password') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
              <p class="mt-1 text-xs text-slate-500">Minimal sesuai kebijakan keamanan (huruf, angka, dst.).</p>
            </div>

            {{-- Konfirmasi Password --}}
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
              <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                     class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">
              @error('password_confirmation') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <button id="submitBtn" type="submit"
              class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed">
              <svg id="spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
              </svg>
              <span>Reset Password</span>
            </button>

            {{-- Link balik --}}
            <div class="text-center text-sm text-slate-600">
              Ingat kata sandi? @if (Route::has('login'))
                <a class="font-medium text-blue-700 hover:text-blue-800 underline" href="{{ route('login') }}">Masuk</a>
              @endif
            </div>
          </form>
        </div>

        {{-- Footer --}}
        <div class="mt-6 text-center text-xs text-slate-500">
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
