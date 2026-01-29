{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Akun • Human.Careers</title>
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
            Buat Akun • Human.Careers
          </span>
        </div>

        {{-- Error global (validasi) --}}
        @if ($errors->any())
          <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800" role="alert" aria-live="assertive">
            <div class="flex items-start gap-2">
              <svg class="mt-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 1 21h22L12 2Zm1 15h-2v2h2v-2Zm0-8h-2v6h2V9Z"/></svg>
              <div>Ada beberapa isian yang perlu dicek. Silakan perbaiki dan kirim ulang.</div>
            </div>
          </div>
        @endif

        <div class="relative">
          {{-- Ring luar animasi --}}
          <div class="absolute -inset-[2px] rounded-2xl bg-[conic-gradient(var(--tw-gradient-stops))] from-blue-500 via-teal-400 to-emerald-400 animate-[spin-slow_8s_linear_infinite] opacity-20"></div>

          <div class="relative rounded-2xl bg-white/80 p-6 shadow-xl ring-1 ring-slate-200/80 backdrop-blur">
            <form id="registerForm" method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
              @csrf

              {{-- Honeypot --}}
              <div class="hidden" aria-hidden="true">
                <label for="company" class="sr-only">Company</label>
                <input type="text" id="company" name="company" tabindex="-1" autocomplete="off">
              </div>

              {{-- Nama --}}
              <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                <div class="mt-1 relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 2.5-9 5.5V22h18v-2.5C21 16.5 17 14 12 14Z"/></svg>
                  </span>
                  <input id="name" type="text" name="name" value="{{ old('name') }}" required
                         autocomplete="name" placeholder="Nama sesuai KTP"
                         class="block w-full rounded-lg border-slate-300 pl-9 shadow-sm focus:border-blue-600 focus:ring-blue-600"/>
                </div>
                @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Email --}}
              <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <div class="mt-1 relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4a2 2 0 0 0-2 2v.35l10 6.25L22 6.35V6a2 2 0 0 0-2-2Zm0 5.23-8 5-8-5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.23Z"/></svg>
                  </span>
                  <input id="email" type="email" name="email" value="{{ old('email') }}" required
                         inputmode="email" autocapitalize="none" autocomplete="username" spellcheck="false"
                         placeholder="nama@email.com"
                         class="block w-full rounded-lg border-slate-300 pl-9 shadow-sm focus:border-blue-600 focus:ring-blue-600"/>
                </div>
                @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Password + toggle + strength meter --}}
              <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <div class="relative mt-1">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1a5 5 0 0 0-5 5v3H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-2V6a5 5 0 0 0-5-5Zm3 8H9V6a3 3 0 1 1 6 0v3Z"/></svg>
                  </span>
                  <input id="password" type="password" name="password" required autocomplete="new-password"
                         placeholder="Minimal 8 karakter"
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

                {{-- Strength meter --}}
                <div class="mt-2">
                  <div class="h-1.5 w-full rounded bg-slate-200 overflow-hidden">
                    <div id="pwBar" class="h-full w-0 bg-rose-500 transition-all"></div>
                  </div>
                  <div id="pwHint" class="mt-1 text-[11px] text-slate-500">
                    Gunakan kombinasi huruf besar/kecil, angka, dan simbol.
                  </div>
                </div>

                @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Konfirmasi Password + toggle --}}
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                <div class="relative mt-1">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1a5 5 0 0 0-5 5v3H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-2V6a5 5 0 0 0-5-5Z"/></svg>
                  </span>
                  <input id="password_confirmation" type="password" name="password_confirmation" required
                         autocomplete="new-password" placeholder="Ulangi password"
                         class="block w-full rounded-lg border-slate-300 pr-10 pl-9 shadow-sm focus:border-blue-600 focus:ring-blue-600"/>
                  <button type="button" id="togglePw2"
                          class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-700"
                          aria-label="Tampilkan password konfirmasi">
                    <svg id="eyeOn2" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 5 12 5c4.642 0 8.579 2.51 9.964 6.678.07.214.07.452 0 .644C20.579 16.49 16.642 19 12 19c-4.642 0-8.579-2.51-9.964-6.678z"/>
                      <circle cx="12" cy="12" r="3" stroke-width="1.5" />
                    </svg>
                    <svg id="eyeOff2" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3l18 18M10.585 10.586A2 2 0 0112 10c3.333 0 6.19 1.667 8 4M6.1 6.1C4.486 7.158 3.16 8.691 2.036 10.678 3.423 16.49 7.36 19 12 19"/>
                    </svg>
                  </button>
                </div>
                @error('password_confirmation') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Persetujuan --}}
              <div class="flex items-start gap-2">
                <input id="agree" type="checkbox" class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                <label for="agree" class="text-sm text-slate-600">
                  Saya menyetujui <a href="#" class="underline hover:text-slate-800">Ketentuan Layanan</a> dan
                  <a href="#" class="underline hover:text-slate-800">Kebijakan Privasi</a>.
                </label>
              </div>

              {{-- Submit --}}
              <button id="submitBtn" type="submit"
                class="group w-full inline-flex justify-center items-center gap-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition">
                <svg id="spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
                </svg>
                <span>Buat Akun</span>
                <svg class="h-4 w-4 opacity-80 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M13.172 12 8.222 7.05l1.414-1.414L16 12l-6.364 6.364-1.414-1.414z"/>
                </svg>
              </button>

              {{-- Divider SSO (opsional) --}}
              @if (Route::has('auth.microsoft.redirect') || Route::has('auth.google.redirect'))
                <div class="relative py-2">
                  <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                  <div class="relative flex justify-center"><span class="bg-white/80 px-3 text-xs text-slate-500">atau</span></div>
                </div>
                <div class="grid grid-cols-1 gap-3">
                  @if (Route::has('auth.microsoft.redirect'))
                  <a href="{{ route('auth.microsoft.redirect') }}"
                     class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <img src="https://www.svgrepo.com/show/475692/microsoft-color.svg" alt="" class="h-4 w-4" loading="lazy">
                    Daftar dengan Microsoft
                  </a>
                  @endif
                  @if (Route::has('auth.google.redirect'))
                  <a href="{{ route('auth.google.redirect') }}"
                     class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="" class="h-4 w-4" loading="lazy">
                    Daftar dengan Google
                  </a>
                  @endif
                </div>
              @endif

              {{-- Link ke Login --}}
              @if (Route::has('login'))
                <div class="text-center text-sm text-slate-600">
                  Sudah punya akun?
                  <a class="font-medium text-blue-700 hover:text-blue-800 underline" href="{{ route('login') }}">Masuk</a>
                </div>
              @endif
            </form>

            {{-- Catatan keamanan --}}
            <div class="mt-6 text-center text-xs text-slate-500">
              Data Anda dilindungi. Kami menerapkan enkripsi TLS dan audit akses berkala.
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

  {{-- JS: toggle pw, strength meter, cegah double submit, syarat setuju --}}
  <script>
    (function () {
      const pw = document.getElementById('password');
      const pc = document.getElementById('password_confirmation');
      const email = document.getElementById('email');
      const name = document.getElementById('name');
      const toggle1 = document.getElementById('togglePw');
      const toggle2 = document.getElementById('togglePw2');
      const eyeOn1 = document.getElementById('eyeOn');
      const eyeOff1 = document.getElementById('eyeOff');
      const eyeOn2 = document.getElementById('eyeOn2');
      const eyeOff2 = document.getElementById('eyeOff2');
      const form = document.getElementById('registerForm');
      const btn = document.getElementById('submitBtn');
      const spinner = document.getElementById('spinner');
      const bar = document.getElementById('pwBar');
      const hint = document.getElementById('pwHint');
      const agree = document.getElementById('agree');

      // toggle
      function bindToggle(input, on, off, btn) {
        if (!input || !on || !off || !btn) return;
        btn.addEventListener('click', function(){
          const isText = input.getAttribute('type') === 'text';
          input.setAttribute('type', isText ? 'password' : 'text');
          on.classList.toggle('hidden', !isText);
          off.classList.toggle('hidden', isText);
        });
      }
      bindToggle(pw, eyeOn1, eyeOff1, toggle1);
      bindToggle(pc, eyeOn2, eyeOff2, toggle2);

      // strength meter sederhana
      function scorePassword(s) {
        let score = 0;
        if (!s) return 0;
        const letters = {};
        for (let i=0; i<s.length; i++) { letters[s[i]] = (letters[s[i]] || 0) + 1; score += 5.0 / letters[s[i]]; }
        const variations = {
          digits: /\d/.test(s),
          lower: /[a-z]/.test(s),
          upper: /[A-Z]/.test(s),
          nonWords: /\W/.test(s),
          length8: s.length >= 8
        };
        let variationCount = 0;
        for (const check in variations) variationCount += (variations[check] === true) ? 1 : 0;
        score += (variationCount - 1) * 10;
        return parseInt(score);
      }
      function renderStrength(v) {
        const w = Math.max(5, Math.min(100, v));
        bar.style.width = w + '%';
        if (v < 30) { bar.className = 'h-full bg-rose-500'; hint.textContent = 'Lemah — tambah panjang & variasi karakter.'; }
        else if (v < 60) { bar.className = 'h-full bg-amber-500'; hint.textContent = 'Cukup — tambahkan huruf besar/angka/simbol.'; }
        else if (v < 80) { bar.className = 'h-full bg-teal-500'; hint.textContent = 'Baik — sudah cukup aman.'; }
        else { bar.className = 'h-full bg-emerald-600'; hint.textContent = 'Kuat — pertahankan kerahasiaan kata sandi.'; }
      }
      pw?.addEventListener('input', () => renderStrength(scorePassword(pw.value)));

      // cegah double submit + wajib setuju
      form?.addEventListener('submit', function(e){
        if (agree && !agree.checked) {
          e.preventDefault();
          agree.focus();
          alert('Mohon centang persetujuan Ketentuan Layanan & Kebijakan Privasi.');
          return;
        }
        btn?.setAttribute('disabled','disabled');
        spinner?.classList.remove('hidden');
      }, { once: true });

      // fokus awal
      if (!name?.value) name?.focus(); else if (!email?.value) email?.focus(); else pw?.focus();
    })();
  </script>
</body>
</html>
