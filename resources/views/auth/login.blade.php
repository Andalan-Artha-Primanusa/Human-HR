{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk • Human.Careers</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- === Vite build tanpa @vite (baca manifest.json) === --}}
@php
  $manifestPath = public_path('build/manifest.json');
  $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
  // Ganti key di bawah kalau entry kamu beda (mis. resources/ts/app.ts)
  $entry = $manifest['resources/js/app.js'] ?? null;
@endphp

@if($entry)
  @foreach(($entry['css'] ?? []) as $css)
    <link rel="stylesheet" href="{{ asset('build/'.$css) }}">
  @endforeach
  <script type="module" src="{{ asset('build/'.$entry['file']) }}"></script>
@else
  {{-- Fallback opsional: boleh dihapus kalau tidak diperlukan --}}
  {{-- <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}"> --}}
  {{-- <script type="module" src="{{ asset('build/assets/app.js') }}"></script> --}}
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
    /* 🔔 shake utk salah password */
    @keyframes shake {
      0%,100% { transform: translateX(0) }
      20% { transform: translateX(-4px) }
      40% { transform: translateX(4px) }
      60% { transform: translateX(-3px) }
      80% { transform: translateX(3px) }
    }
    .shake { animation: shake .35s ease-in-out 1; }
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
      <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-gradient-to-tr from-blue-500/20 via-teal-400/20 to-emerald-400/20 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-28 -left-20 h-64 w-64 rounded-full bg-gradient-to-tr from-pink-400/20 via-purple-400/20 to-indigo-400/20 blur-3xl"></div>

      <div class="w-full max-w-md">
        {{-- Brand --}}
        <div class="mb-8 flex flex-col items-center justify-center gap-3">
          <img src="{{ asset('assets/foto2.png') }}" alt="Andalan"
               class="h-20 sm:h-24 md:h-28 w-auto" loading="lazy">
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-1 text-xs font-medium shadow-sm backdrop-blur">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M10.242 16.313 6.343 12.414l1.414-1.414 2.485 2.485 6.364-6.364 1.414 1.414z"/></svg>
            Portal Karier Resmi • Human.Careers
          </span>
        </div>

        {{-- Session Status (mis. selesai reset password) --}}
        @if (session('status'))
          <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800">
            {{ session('status') }}
          </div>
        @endif

        {{-- 🚨 Auth Error (salah email/password, throttle, dsb) --}}
        @php
          // Breeze/Jetstream biasanya meletakkan pesan ke error bag 'email'
          $authError = $errors->first('email');
        @endphp
        @if ($authError)
          <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800" role="alert" aria-live="assertive">
            <div class="flex items-start gap-2">
              <svg class="mt-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 1 21h22L12 2Zm1 15h-2v2h2v-2Zm0-8h-2v6h2V9Z"/></svg>
              <div>
                {{ $authError }}
                {{-- Pesan umum ramah: tampil jika bukan throttle --}}
                @if (!Str::contains($authError, ['Too many', 'terlalu banyak']))
                  <div class="text-[11px] text-rose-700/80 mt-1">Pastikan email dan kata sandi benar. Periksa tombol Caps Lock dan coba lagi.</div>
                @endif
              </div>
            </div>
          </div>
        @endif

        {{-- Kartu --}}
        <div class="relative {{ $authError ? 'shake' : '' }}">
          <div class="absolute -inset-[2px] rounded-2xl bg-[conic-gradient(var(--tw-gradient-stops))] from-blue-500 via-teal-400 to-emerald-400 animate-[spin-slow_8s_linear_infinite] opacity-20"></div>

          <div class="relative rounded-2xl bg-white/80 p-6 shadow-xl ring-1 ring-slate-200/80 backdrop-blur" id="loginCard">
            <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
              @csrf

              {{-- Honeypot --}}
              <div class="hidden" aria-hidden="true">
                <label for="company" class="sr-only">Company</label>
                <input type="text" id="company" name="company" tabindex="-1" autocomplete="off">
              </div>

              {{-- Email --}}
              <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <div class="mt-1 relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M20 4H4a2 2 0 0 0-2 2v.35l10 6.25L22 6.35V6a2 2 0 0 0-2-2Zm0 5.23-8 5-8-5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.23Z"/>
                    </svg>
                  </span>
                  <input id="email" type="email" name="email" value="{{ old('email') }}" required
                         autofocus inputmode="email" autocapitalize="none" autocomplete="username"
                         spellcheck="false" placeholder="nama@perusahaan.com"
                         class="block w-full rounded-lg border-slate-300 pl-9 shadow-sm focus:border-blue-600 focus:ring-blue-600"
                         aria-describedby="emailHelp"/>
                </div>
                <p id="emailHelp" class="sr-only">Gunakan email kerja Anda</p>
                @error('email')
                  {{-- Jika ada validasi lain pada email, tetap tampil di bawah field --}}
                  <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
              </div>

              {{-- Password + toggle + CapsLock --}}
              <div>
                <div class="flex items-center justify-between">
                  <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                  <span id="capsInfo" class="text-xs text-amber-700 hidden">Caps Lock aktif</span>
                </div>
                <div class="relative mt-1">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    {{-- ikon gembok --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 1a5 5 0 0 0-5 5v3H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-2V6a5 5 0 0 0-5-5Zm3 8H9V6a3 3 0 1 1 6 0v3Z"/>
                    </svg>
                  </span>
                  <input id="password" type="password" name="password" required autocomplete="current-password"
                         placeholder="••••••••"
                         class="block w-full rounded-lg border-slate-300 pr-10 pl-9 shadow-sm focus:border-blue-600 focus:ring-blue-600"
                         aria-describedby="passwordHelp"/>
                  <button type="button" id="togglePw"
                          class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-700"
                          aria-label="Tampilkan password">
                    <svg id="eyeOn" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 5 12 5c4.642 0 8.579 2.51 9.964 6.678.07.214.07.452 0 .644C20.579 16.49 16.642 19 12 19c-4.642 0-8.579-2.51-9.964-6.678z"/>
                      <circle cx="12" cy="12" r="3" stroke-width="1.5" />
                    </svg>
                    <svg id="eyeOff" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3l18 18M10.585 10.586A2 2 0 0112 10c3.333 0 6.19 1.667 8 4M6.1 6.1C4.486 7.158 3.16 8.691 2.036 10.678 3.423 16.49 7.36 19 12 19"/>
                    </svg>
                  </button>
                </div>
                <p id="passwordHelp" class="sr-only">Minimal 8 karakter</p>
                @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Remember & Forgot (dengan ikon) --}}
              <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center gap-2">
                  <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-600" name="remember">
                  <span class="text-sm text-slate-600 inline-flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 0 0-2 2v12l4-4h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z"/></svg>
                    Ingat saya
                  </span>
                </label>

                @if (Route::has('password.request'))
                  <a class="inline-flex items-center gap-1 text-sm font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-700 rounded-md"
                     href="{{ route('password.request') }}" rel="noreferrer">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 8a4 4 0 1 1-4 4H6a6 6 0 1 0 6-6v2Zm1 5h-2v6h2v-6Z"/></svg>
                    Lupa password?
                  </a>
                @endif
              </div>

              {{-- Submit --}}
              <button id="submitBtn" type="submit"
                class="group w-full inline-flex justify-center items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition">
                <svg id="spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
                </svg>
                <span>Masuk</span>
                <svg class="h-4 w-4 opacity-80 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M13.172 12 8.222 7.05l1.414-1.414L16 12l-6.364 6.364-1.414-1.414z"/>
                </svg>
              </button>

              {{-- Divider SSO --}}
              @if (Route::has('auth.microsoft.redirect') || Route::has('auth.google.redirect'))
                <div class="relative py-2">
                  <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                  <div class="relative flex justify-center"><span class="bg-white/80 px-3 text-xs text-slate-500 inline-flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M11 3h2v8h8v2h-8v8h-2v-8H3v-2h8V3Z"/></svg>
                    atau
                  </span></div>
                </div>
                <div class="grid grid-cols-1 gap-3">
                  @if (Route::has('auth.microsoft.redirect'))
                  <a href="{{ route('auth.microsoft.redirect') }}"
                     class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <img src="https://www.svgrepo.com/show/475692/microsoft-color.svg" alt="" class="h-4 w-4" loading="lazy">
                    Lanjutkan dengan Microsoft
                  </a>
                  @endif
                  @if (Route::has('auth.google.redirect'))
                  <a href="{{ route('auth.google.redirect') }}"
                     class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="" class="h-4 w-4" loading="lazy">
                    Lanjutkan dengan Google
                  </a>
                  @endif
                </div>
              @endif

              {{-- 🆕 Register (Buat Akun) --}}
              @if (Route::has('register'))
                <div class="mt-4">
                  <div class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-3">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-2 text-sm text-emerald-900">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 11h10v2H7v-2Zm0 4h7v2H7v-2ZM12 2a5 5 0 0 0-5 5v1H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3v-8a3 3 0 0 0-3-3h-1V7a5 5 0 0 0-5-5Z"/></svg>
                        Pendaftaran akun dibuka hari ini
                      </div>
                      <a href="{{ route('register') }}"
                         class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5v14m-7-7h14"/></svg>
                        Buat Akun
                      </a>
                    </div>
                  </div>
                </div>
              @endif

            </form>

            {{-- Catatan --}}
            <div class="mt-6 text-center text-xs text-slate-500">
              Dengan masuk, Anda menyetujui <a href="#" class="underline hover:text-slate-700">Ketentuan Layanan</a> dan <a href="#" class="underline hover:text-slate-700">Kebijakan Privasi</a>.
            </div>

            {{-- Trust mini --}}
            <div class="mt-4 flex items-center justify-center gap-4 text-[11px] text-slate-500">
              <div class="inline-flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 3 6v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V6l-9-4Z"/></svg>
                Enkripsi TLS
              </div>
              <span>•</span>
              <div class="inline-flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1a9 9 0 0 0-9 9c0 7 9 13 9 13s9-6 9-13a9 9 0 0 0-9-9Zm0 12a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/></svg>
                Data di Indonesia
              </div>
              <span>•</span>
              <div class="inline-flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-slate-600" viewBox="0 0 24 24" fill="currentColor"><path d="M2 4h20v2H2V4Zm2 4h16v12H4V8Zm4 2v8h8v-8H8Z"/></svg>
                Audit Akses
              </div>
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

  {{-- JS: toggle password, CapsLock, double-submit, shortcut, fokus --}}
  <script>
    (function () {
      const pw = document.getElementById('password');
      const email = document.getElementById('email');
      const toggle = document.getElementById('togglePw');
      const eyeOn = document.getElementById('eyeOn');
      const eyeOff = document.getElementById('eyeOff');
      const form = document.getElementById('loginForm');
      const btn = document.getElementById('submitBtn');
      const spinner = document.getElementById('spinner');
      const capsInfo = document.getElementById('capsInfo');

      if (toggle && pw) {
        toggle.addEventListener('click', function () {
          const isText = pw.getAttribute('type') === 'text';
          pw.setAttribute('type', isText ? 'password' : 'text');
          toggle.setAttribute('aria-label', isText ? 'Tampilkan password' : 'Sembunyikan password');
          eyeOn.classList.toggle('hidden', !isText);
          eyeOff.classList.toggle('hidden', isText);
        });
      }

      if (pw && capsInfo) {
        const handler = (e) => {
          if ('getModifierState' in e) {
            const on = e.getModifierState('CapsLock');
            capsInfo.classList.toggle('hidden', !on);
          }
        };
        pw.addEventListener('keyup', handler);
        pw.addEventListener('keydown', handler);
        pw.addEventListener('focus', handler);
        pw.addEventListener('blur', () => capsInfo.classList.add('hidden'));
      }

      if (form && btn && spinner) {
        form.addEventListener('submit', function () {
          btn.setAttribute('disabled', 'disabled');
          spinner.classList.remove('hidden');
        }, { once: true });
      }

      document.addEventListener('keydown', function (e) {
        const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        if ((isMac ? e.metaKey : e.ctrlKey) && e.key === 'Enter') {
          form?.requestSubmit();
        }
      });

      if (email && pw) { if (!email.value) email.focus(); else pw.focus(); }
    })();
  </script>
</body>
</html>
