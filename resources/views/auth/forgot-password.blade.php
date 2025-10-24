{{-- resources/views/auth/forgot-password.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lupa Password • Human.Careers</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php
  $manifestFile = public_path('build/manifest.json');
  $manifest = is_file($manifestFile) ? json_decode(file_get_contents($manifestFile), true) : [];

  // entry utama sesuai vite.config (umumnya resources/js/app.js)
  $entry = $manifest['resources/js/app.js'] ?? null;
@endphp

@if($entry)
  {{-- CSS yang dibundel --}}
  @foreach(($entry['css'] ?? []) as $css)
    <link rel="stylesheet" href="{{ asset('build/'.$css) }}">
  @endforeach

  {{-- JS (module) --}}
  <script type="module" src="{{ asset('build/'.$entry['file']) }}"></script>
@else
  {{-- Fallback aman kalau manifest belum ada/terupload --}}
  <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
  <script type="module" src="{{ asset('build/assets/app.js') }}"></script>
@endif
  <style>
    [x-cloak]{display:none!important}
    .bg-dots{
      background-image:
        radial-gradient(#e5e7eb 1px, transparent 1px),
        radial-gradient(#e5e7eb 1px, transparent 1px);
      background-position: 0 0, 10px 10px;
      background-size: 20px 20px;
    }
    @keyframes spin-slow { to { transform: rotate(360deg); } }
  </style>
</head>
<body class="min-h-screen bg-white text-slate-800">
  <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

    {{-- Kiri: foto --}}
    <div class="relative hidden lg:block">
      <img src="{{ asset('assets/hr1.jpg') }}" alt="Aktivitas tim Andalan"
           class="absolute inset-0 h-full w-full object-cover" loading="lazy" referrerpolicy="no-referrer" />
    </div>

    {{-- Kanan: form --}}
    <div class="bg-dots flex min-h-screen items-center justify-center p-6 sm:p-10 relative overflow-hidden">
      {{-- Aura dekoratif --}}
      <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-gradient-to-tr from-blue-500/20 via-teal-400/20 to-emerald-400/20 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-28 -left-20 h-64 w-64 rounded-full bg-gradient-to-tr from-pink-400/20 via-purple-400/20 to-indigo-400/20 blur-3xl"></div>

      <div class="w-full max-w-md">
        {{-- Brand --}}
        <div class="mb-8 flex flex-col items-center justify-center gap-3">
          <img src="{{ asset('assets/foto2.png') }}" alt="Andalan"
               class="h-20 sm:h-24 md:h-28 w-auto" loading="lazy">
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-1 text-xs font-medium shadow-sm backdrop-blur">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M10.242 16.313 6.343 12.414l1.414-1.414 2.485 2.485 6.364-6.364 1.414 1.414z"/></svg>
            Pemulihan Akun • Human.Careers
          </span>
        </div>

        {{-- Info intro --}}
        <div class="mb-4 text-sm text-slate-600">
          Lupa kata sandi? Tidak masalah. Masukkan alamat email Anda dan kami akan mengirim tautan untuk mengatur ulang kata sandi.
        </div>

        {{-- Status sukses (link reset terkirim) --}}
        @if (session('status'))
          <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" role="status" aria-live="polite">
            <div class="flex items-start gap-2">
              <svg class="mt-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 15.172 6.414 11.586 5 13l5 5 9-9-1.414-1.414L10 15.172Z"/></svg>
              <div>
                {{ session('status') }}
                <div class="text-[11px] text-emerald-700/80 mt-1">Jika tidak terlihat di inbox, periksa folder Spam/Promosi.</div>
              </div>
            </div>
          </div>
        @endif

        {{-- Error global (mis. throttle) --}}
        @if ($errors->any())
          <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800" role="alert" aria-live="assertive">
            {{ $errors->first() }}
          </div>
        @endif

        <div class="relative">
          {{-- Ring luar animasi --}}
          <div class="absolute -inset-[2px] rounded-2xl bg-[conic-gradient(var(--tw-gradient-stops))] from-blue-500 via-teal-400 to-emerald-400 animate-[spin-slow_8s_linear_infinite] opacity-20"></div>

          <div class="relative rounded-2xl bg-white/80 p-6 shadow-xl ring-1 ring-slate-200/80 backdrop-blur">
            <form id="forgotForm" method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
              @csrf

              {{-- Honeypot sederhana untuk bot --}}
              <input type="text" name="hp_url" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

              {{-- Email --}}
              <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <div class="mt-1 relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M20 4H4a2 2 0 0 0-2 2v.35l10 6.25L22 6.35V6a2 2 0 0 0-2-2Zm0 5.23-8 5-8-5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.23Z"/>
                    </svg>
                  </span>
                  <input id="email" type="email" name="email" value="{{ old('email') }}" required
                         inputmode="email" autocapitalize="none" autocomplete="email" spellcheck="false"
                         placeholder="nama@email.com"
                         class="block w-full rounded-lg border-slate-300 pl-9 shadow-sm focus:border-blue-600 focus:ring-blue-600"/>
                </div>
                @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Submit --}}
              <button id="submitBtn" type="submit"
                class="group w-full inline-flex justify-center items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition"
                aria-busy="false">
                <svg id="spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
                </svg>
                <span>Kirim Tautan Reset</span>
                <svg class="h-4 w-4 opacity-80 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M13.172 12 8.222 7.05l1.414-1.414L16 12l-6.364 6.364-1.414-1.414z"/>
                </svg>
              </button>

              {{-- Link balik --}}
              <div class="text-center text-sm text-slate-600">
                Ingat kata sandi Anda?
                @if (Route::has('login'))
                  <a class="font-medium text-blue-700 hover:text-blue-800 underline" href="{{ route('login') }}">Masuk</a>
                @endif
                @if (Route::has('register'))
                  • Belum punya akun? <a class="font-medium text-emerald-700 hover:text-emerald-800 underline" href="{{ route('register') }}">Daftar</a>
                @endif
              </div>
            </form>

            {{-- Catatan keamanan --}}
            <div class="mt-6 text-center text-xs text-slate-500">
              Kami melindungi data Anda dengan enkripsi TLS dan audit akses berkala.
            </div>
          </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex items-center justify-center gap-2 text-xs text-slate-500">
          <span>© {{ now()->year }} Andalan Group • Human.Careers</span>
        </div>
      </div>
    </div>
  </div>

  {{-- JS: cegah double submit + fokus --}}
  <script>
    (function () {
      const form = document.getElementById('forgotForm');
      const btn = document.getElementById('submitBtn');
      const spinner = document.getElementById('spinner');
      const email = document.getElementById('email');

      form?.addEventListener('submit', function () {
        // Honeypot quick check (jika terisi, jangan kirim)
        const hp = form.querySelector('input[name="hp_url"]');
        if (hp && hp.value) {
          event?.preventDefault();
          return false;
        }
        btn?.setAttribute('disabled','disabled');
        btn?.setAttribute('aria-busy','true');
        spinner?.classList.remove('hidden');
      }, { once: true });

      if (!email?.value) email?.focus();
    })();
  </script>
</body>
</html>
