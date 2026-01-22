{{-- resources/views/auth/confirm-password.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Konfirmasi Password • Human.Careers</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- === Vite build tanpa @vite (baca manifest.json) === --}}

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
            Konfirmasi Akses • Human.Careers
          </span>
        </div>

        {{-- Intro --}}
        <div class="mb-4 text-sm text-slate-600">
          Ini area aman aplikasi. Mohon masukkan password Anda untuk melanjutkan.
        </div>

        {{-- Error global (mis. salah password) --}}
        @if ($errors->has('password'))
          <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800" role="alert" aria-live="assertive">
            <div class="flex items-start gap-2">
              <svg class="mt-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 1 21h22L12 2Zm1 15h-2v2h2v-2Zm0-8h-2v6h2V9Z"/></svg>
              <div>Password tidak sesuai. Periksa tombol Caps Lock dan coba lagi.</div>
            </div>
          </div>
        @endif

        <div class="relative">
          {{-- Ring luar animasi --}}
          <div class="absolute -inset-[2px] rounded-2xl bg-[conic-gradient(var(--tw-gradient-stops))] from-blue-500 via-teal-400 to-emerald-400 animate-[spin-slow_8s_linear_infinite] opacity-20"></div>

          <div class="relative rounded-2xl bg-white/80 p-6 shadow-xl ring-1 ring-slate-200/80 backdrop-blur">
            <form id="confirmForm" method="POST" action="{{ route('password.confirm') }}" class="space-y-5" novalidate>
              @csrf

              {{-- Password + toggle + CapsLock --}}
              <div>
                <div class="flex items-center justify-between">
                  <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                  <span id="capsInfo" class="text-xs text-amber-700 hidden">Caps Lock aktif</span>
                </div>
                <div class="relative mt-1">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 1a5 5 0 0 0-5 5v3H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-2V6a5 5 0 0 0-5-5Zm3 8H9V6a3 3 0 1 1 6 0v3Z"/>
                    </svg>
                  </span>
                  <input id="password" type="password" name="password" required
                         autocomplete="current-password" placeholder="••••••••"
                         class="block w-full rounded-lg border-slate-300 pr-10 pl-9 shadow-sm focus:border-blue-600 focus:ring-blue-600"/>
                  <button type="button" id="togglePw"
                          class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-700"
                          aria-label="Tampilkan password">
                    <svg id="eyeOn" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 5 12 5c4.642 0 8.579 2.51 9.964 6.678.07.214.07.452 0 .644C20.579 16.49 16.642 19 12 19c-4.642 0-8.579-2.51-9.964-6.678z"/>
                      <circle cx="12" cy="12" r="3" stroke-width="1.5" />
                    </svg>
                    <svg id="eyeOff" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3l18 18M10.585 10.586A2 2 0 0112 10c3.333 0 6.19 1.667 8 4M6.1 6.1C4.486 7.158 3.16 8.691 2.036 10.678 3.423 16.49 7.36 19 12 19"/>
                    </svg>
                  </button>
                </div>
                @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Aksi --}}
              <div class="flex items-center justify-between">
                @if (Route::has('password.request'))
                  <a class="inline-flex items-center gap-1 text-sm font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-700 rounded-md"
                     href="{{ route('password.request') }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 8a4 4 0 1 1-4 4H6a6 6 0 1 0 6-6v2Zm1 5h-2v6h2v-6Z"/></svg>
                    Lupa password?
                  </a>
                @endif>

                <button id="submitBtn" type="submit"
                  class="group inline-flex justify-center items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition">
                  <svg id="spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
                  </svg>
                  <span>Konfirmasi</span>
                </button>
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

  {{-- JS: toggle password, CapsLock, cegah double submit, fokus --}}
  <script>
    (function () {
      const pw = document.getElementById('password');
      const toggle = document.getElementById('togglePw');
      const eyeOn = document.getElementById('eyeOn');
      const eyeOff = document.getElementById('eyeOff');
      const form = document.getElementById('confirmForm');
      const btn = document.getElementById('submitBtn');
      const spinner = document.getElementById('spinner');
      const capsInfo = document.getElementById('capsInfo');

      // Toggle password
      if (toggle && pw) {
        toggle.addEventListener('click', function () {
          const isText = pw.getAttribute('type') === 'text';
          pw.setAttribute('type', isText ? 'password' : 'text');
          toggle.setAttribute('aria-label', isText ? 'Tampilkan password' : 'Sembunyikan password');
          eyeOn.classList.toggle('hidden', !isText);
          eyeOff.classList.toggle('hidden', isText);
        });
      }

      // CapsLock indicator
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

      // Cegah double submit + spinner
      form?.addEventListener('submit', function () {
        btn?.setAttribute('disabled','disabled');
        spinner?.classList.remove('hidden');
      }, { once: true });

      // Fokus awal
      if (!pw?.value) pw?.focus();
    })();
  </script>
</body>
</html>
