{{-- resources/views/auth/verify-email.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verifikasi Email • Human.Careers</title>
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
  </style>
</head>
<body class="min-h-screen bg-white text-slate-800">
  <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

    {{-- Kiri: foto --}}
    <div class="relative hidden lg:block">
      <img src="{{ asset('assets/hr1.jpg') }}" alt="Aktivitas tim Andalan"
           class="absolute inset-0 h-full w-full object-cover" loading="lazy" referrerpolicy="no-referrer" />
    </div>

    {{-- Kanan: konten --}}
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
            Verifikasi Email • Human.Careers
          </span>
        </div>

        {{-- Intro --}}
        <div class="mb-4 text-sm text-slate-600">
          Terima kasih telah mendaftar! Sebelum mulai, mohon verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirim.
          Jika belum menerima email, Anda dapat mengirim ulang tautan verifikasi.
        </div>

        {{-- Info email yang digunakan --}}
        @php $userEmail = auth()->user()->email ?? null; @endphp
        @if ($userEmail)
          <div class="mb-4 rounded-lg border border-slate-200 bg-white/70 px-3 py-2 text-xs text-slate-700 backdrop-blur">
            Dikirim ke: <span class="font-medium">{{ $userEmail }}</span>
          </div>
        @endif

        {{-- Status sukses kirim ulang --}}
        @if (session('status') == 'verification-link-sent')
          <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" role="status" aria-live="polite">
            <div class="flex items-start gap-2">
              <svg class="mt-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 15.172 6.414 11.586 5 13l5 5 9-9-1.414-1.414L10 15.172Z"/></svg>
              <div>Tautan verifikasi baru telah dikirim ke email yang Anda gunakan saat pendaftaran.</div>
            </div>
          </div>
        @endif

        <div class="relative">
          {{-- Ring luar animasi --}}
          <div class="absolute -inset-[2px] rounded-2xl bg-[conic-gradient(var(--tw-gradient-stops))] from-blue-500 via-teal-400 to-emerald-400 animate-[spin-slow_8s_linear_infinite] opacity-20"></div>

          <div class="relative rounded-2xl bg-white/80 p-6 shadow-xl ring-1 ring-slate-200/80 backdrop-blur">
            <div class="space-y-5">
              {{-- Tips kecil --}}
              <div class="text-xs text-slate-600">
                Tidak terlihat di Inbox? Periksa folder <span class="font-medium">Spam/Promosi</span> atau tunggu 1–2 menit.
              </div>

              <div class="flex items-center justify-between gap-3">
                {{-- Form kirim ulang --}}
                <form id="resendForm" method="POST" action="{{ route('verification.send') }}" class="inline-flex">
                  @csrf
                  <button id="resendBtn" type="submit"
                          class="group inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition">
                    <svg id="resendSpinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
                    </svg>
                    <span id="resendLabel">Kirim Ulang Tautan</span>
                  </button>
                </form>

                {{-- Form logout --}}
                <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                  @csrf
                  <button type="submit"
                          class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5v-2H5V5h5V3Zm5.707 4.293L14.293 8.707 16.586 11H9v2h7.586l-2.293 2.293 1.414 1.414L21.414 12l-5.707-5.707Z"/></svg>
                    Keluar
                  </button>
                </form>
              </div>

              {{-- Opsi ubah email (opsional, jika ada route/profile) --}}
              @if (Route::has('profile.edit'))
                <div class="text-xs text-slate-600">
                  Salah alamat? <a href="{{ route('profile.edit') }}" class="font-medium text-blue-700 hover:text-blue-800 underline">Ubah email akun</a>, lalu kirim ulang.
                </div>
              @endif
            </div>

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

  {{-- JS: spinner + cooldown (front-end) --}}
  <script>
    (function () {
      const resendForm = document.getElementById('resendForm');
      const resendBtn = document.getElementById('resendBtn');
      const resendSpinner = document.getElementById('resendSpinner');
      const resendLabel = document.getElementById('resendLabel');

      // Cooldown klien sederhana untuk cegah spam klik (mis. 30 detik)
      const COOLDOWN = 30; // detik
      let timer = null;

      function startCooldown() {
        let left = COOLDOWN;
        resendBtn.disabled = true;
        updateLabel(left);
        timer = setInterval(() => {
          left -= 1;
          if (left <= 0) {
            clearInterval(timer);
            timer = null;
            resendBtn.disabled = false;
            resendLabel.textContent = 'Kirim Ulang Tautan';
          } else {
            updateLabel(left);
          }
        }, 1000);
      }

      function updateLabel(left) {
        resendLabel.textContent = `Tunggu ${left}s…`;
      }

      resendForm?.addEventListener('submit', function () {
        resendBtn?.setAttribute('disabled','disabled');
        resendSpinner?.classList.remove('hidden');
        // biarkan server memproses; begitu halaman reload (status terkirim), cooldown bisa mulai lagi via script inline:
        sessionStorage.setItem('verify_cooldown_start', Date.now().toString());
      }, { once: true });

      // Jika habis kirim, aktifkan cooldown saat kembali ke halaman ini
      const last = parseInt(sessionStorage.getItem('verify_cooldown_start') || '0', 10);
      if (last) {
        const elapsed = Math.floor((Date.now() - last) / 1000);
        if (elapsed < COOLDOWN) {
          const left = COOLDOWN - elapsed;
          resendBtn.disabled = true;
          resendLabel.textContent = `Tunggu ${left}s…`;
          let leftNow = left;
          timer = setInterval(() => {
            leftNow -= 1;
            if (leftNow <= 0) {
              clearInterval(timer);
              timer = null;
              resendBtn.disabled = false;
              resendLabel.textContent = 'Kirim Ulang Tautan';
              sessionStorage.removeItem('verify_cooldown_start');
            } else {
              resendLabel.textContent = `Tunggu ${leftNow}s…`;
            }
          }, 1000);
        } else {
          sessionStorage.removeItem('verify_cooldown_start');
        }
      }
    })();
  </script>
</body>
</html>
