{{-- resources/views/auth/verify-code.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Verifikasi Kode • {{ config('app.name', 'Human.Careers') }}</title>

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

  {{-- Vite --}}
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
<body class="font-sans text-slate-800 antialiased min-h-screen bg-white">
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
        {{-- Brand/logo --}}
        <div class="mb-8 flex flex-col items-center justify-center gap-3">
          <a href="{{ url('/') }}" class="inline-flex items-center justify-center">
            @if (class_exists(\App\View\Components\ApplicationLogo::class))
              <x-application-logo class="h-20 w-20 text-slate-500" />
            @else
              <img src="{{ asset('assets/foto2.png') }}" alt="Logo" class="h-20 w-auto" loading="lazy">
            @endif
          </a>
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-1 text-xs font-medium shadow-sm backdrop-blur">
            {{ config('app.name', 'Human.Careers') }} • Verifikasi Kode
          </span>
        </div>

        {{-- Status --}}
        @if (session('status') == 'verification-link-sent')
          <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
            Kode verifikasi baru telah dikirim ke email Anda.
          </div>
        @endif
        @error('resend')
          <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">{{ $message }}</div>
        @enderror

        {{-- Kartu --}}
        <div class="relative">
          <div class="absolute -inset-[2px] rounded-2xl bg-[conic-gradient(var(--tw-gradient-stops))] from-blue-500 via-teal-400 to-emerald-400 animate-[spin-slow_8s_linear_infinite] opacity-20"></div>
          <div class="relative rounded-2xl bg-white/80 p-6 shadow-xl ring-1 ring-slate-200/80 backdrop-blur">

            <h1 class="text-lg font-semibold text-slate-800 mb-1">Masukkan Kode Verifikasi</h1>
            <p class="text-sm text-slate-600 mb-4">
              Kami mengirim 6 digit kode ke email: <span class="font-medium">{{ auth()->user()->email }}</span>
            </p>

            {{-- Form verifikasi --}}
            <form method="POST" action="{{ route('verification.code.verify') }}" class="space-y-4" id="verifyForm">
              @csrf
              <div>
                <label for="code" class="block text-sm font-medium text-slate-700">Kode 6 Digit</label>
                <input id="code" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" required
                       placeholder="123456"
                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">
                @error('code') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="flex items-center justify-between">
                {{-- Form kirim ulang (terpisah, bukan nested) --}}
                <form method="POST" action="{{ route('verification.code.resend') }}">
                  @csrf
                  <button type="submit" class="text-sm text-blue-700 hover:text-blue-800 underline">
                    Kirim ulang kode
                  </button>
                </form>

                <button id="submitBtn" type="submit"
                  class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
                  Verifikasi
                </button>
              </div>
            </form>

            {{-- Catatan keamanan --}}
            <div class="mt-6 text-center text-xs text-slate-500">
              Data Anda dilindungi oleh enkripsi TLS & audit akses berkala.
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

  {{-- JS kecil: fokus & validasi angka --}}
  <script>
    (function(){
      const input = document.getElementById('code');
      input?.focus();
      input?.addEventListener('input', () => {
        input.value = input.value.replace(/\D+/g,'').slice(0,6);
      });
    })();
  </script>
</body>
</html>
