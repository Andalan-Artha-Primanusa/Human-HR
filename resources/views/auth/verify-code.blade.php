{{-- resources/views/auth/verify-code.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Verifikasi Kode • {{ config('app.name', 'Human.Careers') }}</title>

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  {{-- Vite --}}
  {{-- === Vite build tanpa @vite (baca manifest.json) === --}}


  <style>
    [x-cloak] {
      display: none !important
    }

    .bg-dots {
      background-image:
        radial-gradient(#e5e7eb 1px, transparent 1px),
        radial-gradient(#e5e7eb 1px, transparent 1px);
      background-position: 0 0, 10px 10px;
      background-size: 20px 20px;
    }

    @keyframes spin-slow {
      to {
        transform: rotate(360deg);
      }
    }
  </style>
</head>

<body class="min-h-screen font-sans antialiased bg-white text-slate-800">
  <div class="grid min-h-screen grid-cols-1 lg:grid-cols-2">

    {{-- Kiri: foto --}}
    <div class="relative hidden lg:block">
      <img src="{{ asset('assets/hr1.jpg') }}" alt="Aktivitas tim Andalan"
        class="absolute inset-0 object-cover w-full h-full" loading="lazy" referrerpolicy="no-referrer" />
    </div>

    {{-- Kanan: konten --}}
    <div class="relative flex items-center justify-center min-h-screen p-6 overflow-hidden bg-dots sm:p-10">
      {{-- Aura dekoratif --}}
      <div class="absolute rounded-full pointer-events-none -top-24 -right-24 h-72 w-72 bg-gradient-to-tr from-blue-500/20 via-teal-400/20 to-emerald-400/20 blur-3xl"></div>
      <div class="absolute w-64 h-64 rounded-full pointer-events-none -bottom-28 -left-20 bg-gradient-to-tr from-pink-400/20 via-purple-400/20 to-indigo-400/20 blur-3xl"></div>

      <div class="w-full max-w-md">
        {{-- Brand/logo --}}
        <div class="flex flex-col items-center justify-center gap-3 mb-8">
          <a href="{{ url('/') }}" class="inline-flex items-center justify-center">
            @if (class_exists(\App\View\Components\ApplicationLogo::class))
            <x-application-logo class="w-20 h-20 text-slate-500" />
            @else
            @endif
          </a>
          <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-medium border rounded-full shadow-sm border-slate-200 bg-white/70 backdrop-blur">
            {{ config('app.name', 'Human.Careers') }} • Verifikasi Kode
          </span>
        </div>

        {{-- Status --}}
        @if (session('status') == 'verification-link-sent')
        <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-emerald-200 bg-emerald-50 text-emerald-800">
          Kode verifikasi baru telah dikirim ke email Anda.
        </div>
        @endif
        @error('resend')
        <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-amber-200 bg-amber-50 text-amber-800">{{ $message }}</div>
        @enderror

        {{-- Kartu --}}
        <div class="relative">
          <div class="absolute -inset-[2px] rounded-2xl bg-[conic-gradient(var(--tw-gradient-stops))] from-blue-500 via-teal-400 to-emerald-400 animate-[spin-slow_8s_linear_infinite] opacity-20"></div>
          <div class="relative p-6 shadow-xl rounded-2xl bg-white/80 ring-1 ring-slate-200/80 backdrop-blur">

            <h1 class="mb-1 text-lg font-semibold text-slate-800">Masukkan Kode Verifikasi</h1>
            <p class="mb-4 text-sm text-slate-600">
              Kami mengirim 6 digit kode ke email: <span class="font-medium">{{ auth()->user()->email }}</span>
            </p>

            {{-- Form verifikasi --}}
            {{-- FORM VERIFIKASI --}}
            <form method="POST" action="{{ route('verification.code.verify') }}" class="space-y-4" id="verifyForm">
              @csrf

              <div>
                <label for="code" class="block text-sm font-medium text-slate-700">
                  Kode 6 Digit
                </label>
                <input id="code" name="code" required
                  inputmode="numeric" pattern="\d{6}" maxlength="6"
                  class="block w-full mt-1 rounded-lg shadow-sm border-slate-300">
              </div>

              <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white">
                Verifikasi
              </button>
            </form>

            {{-- FORM RESEND (TERPISAH, AMAN) --}}
            <form method="POST" action="{{ route('verification.code.resend') }}" class="mt-4 text-center">
              @csrf
              <button type="submit"
                class="text-sm text-blue-700 underline hover:text-blue-800">
                Kirim ulang kode
              </button>
            </form>


            {{-- Catatan keamanan --}}
            <div class="mt-6 text-xs text-center text-slate-500">
              Data Anda dilindungi oleh enkripsi TLS & audit akses berkala.
            </div>
          </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-center gap-2 mt-6 text-xs text-slate-500">
          <span>© {{ now()->year }} Andalan Group • Human.Careers</span>
        </div>
      </div>
    </div>
  </div>

  {{-- JS kecil: fokus & validasi angka --}}
  <script>
    (function() {
      const input = document.getElementById('code');
      input?.focus();
      input?.addEventListener('input', () => {
        input.value = input.value.replace(/\D+/g, '').slice(0, 6);
      });
    })();
  </script>
</body>

</html>