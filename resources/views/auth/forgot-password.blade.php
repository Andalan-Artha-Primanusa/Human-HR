<!DOCTYPE html>
<html lang="id">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lupa Password • Human.Careers</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; }
    html, body {
      margin: 0; padding: 0;
      font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
      -webkit-font-smoothing: antialiased;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp .45s ease both; }

    .auth-input {
      width: 100%;
      padding: .7rem 1rem;
      border-radius: .625rem;
      border: 1.5px solid rgba(167,125,82,.25);
      background: #fff;
      color: #3b2209;
      font-size: .875rem;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .auth-input::placeholder { color: #c4a882; }
    .auth-input:focus {
      border-color: #a77d52;
      box-shadow: 0 0 0 3px rgba(167,125,82,.15);
    }
    .auth-label {
      display: block;
      font-size: .78rem;
      font-weight: 600;
      color: #5c3d1e;
      margin-bottom: .3rem;
      letter-spacing: .02em;
    }
    .auth-btn {
      width: 100%;
      padding: .8rem;
      background: #a77d52;
      color: #fff;
      border: none;
      border-radius: .625rem;
      font-size: .9rem;
      font-weight: 700;
      cursor: pointer;
      transition: opacity .2s, transform .15s, box-shadow .2s;
      box-shadow: 0 4px 14px rgba(167,125,82,.35);
    }
    .auth-btn:hover  { opacity: .92; box-shadow: 0 6px 20px rgba(167,125,82,.45); }
    .auth-btn:active { transform: scale(.98); }
    .auth-btn:disabled {
      opacity: .45;
      cursor: not-allowed;
      box-shadow: none;
    }

    .field-error {
      margin-top: .3rem;
      font-size: .75rem;
      color: #b91c1c;
    }

    .alert-success {
      padding: .75rem 1rem;
      border-radius: .625rem;
      background: #ecfdf5;
      border: 1.5px solid rgba(16,185,129,.25);
      font-size: .82rem;
      color: #065f46;
      margin-bottom: 1.25rem;
    }
  </style>
</head>

<body style="background:#fff; min-height:100vh; display:flex; align-items:stretch;">

  <div style="display:flex; width:100%; min-height:100vh;">

    {{-- ===== PANEL KIRI ===== --}}
    <div class="hidden lg:flex" style="flex:1; position:relative; overflow:hidden; flex-direction:column; align-items:center; justify-content:center; padding:3rem;">
      {{-- Background foto --}}
      <img src="{{ asset('assets/hr1.jpg') }}" alt="" aria-hidden="true"
        style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; object-position:center;">
      {{-- Overlay #a77d52 --}}
      <div style="position:absolute; inset:0; background:rgba(167,125,82,0.82);"></div>
      <div style="position:absolute; top:-80px; right:-80px; width:320px; height:320px; border-radius:50%; background:rgba(255,255,255,.08);"></div>
      <div style="position:absolute; bottom:-60px; left:-60px; width:240px; height:240px; border-radius:50%; background:rgba(255,255,255,.06);"></div>
      <div style="position:absolute; top:35%; right:-20px; width:160px; height:160px; border-radius:50%; background:rgba(255,255,255,.05);"></div>

      <div style="position:relative; z-index:1; text-align:center; max-width:400px;">
        <h1 style="color:#fff; font-size:1.9rem; font-weight:800; margin:0 0 1rem; line-height:1.25;">
          Pulihkan Akses<br>Akun Anda
        </h1>
        <p style="color:rgba(255,255,255,.8); font-size:.9rem; line-height:1.75; margin:0 0 2rem;">
          Lupa password? Jangan khawatir, kami akan membantu Anda mengatur ulang password dengan aman dan cepat.
        </p>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.6rem; margin-top:1rem;">
          <div style="background:rgba(255,255,255,.12); border-radius:.875rem; padding:.85rem .5rem; text-align:center;">
            <div style="color:#fff; font-size:1.3rem; font-weight:800;">Aman</div>
            <div style="color:rgba(255,255,255,.72); font-size:.7rem; margin-top:.2rem;">Terenkripsi TLS</div>
          </div>
          <div style="background:rgba(255,255,255,.12); border-radius:.875rem; padding:.85rem .5rem; text-align:center;">
            <div style="color:#fff; font-size:1.3rem; font-weight:800;">Cepat</div>
            <div style="color:rgba(255,255,255,.72); font-size:.7rem; margin-top:.2rem;">Instant Email</div>
          </div>
          <div style="background:rgba(255,255,255,.12); border-radius:.875rem; padding:.85rem .5rem; text-align:center;">
            <div style="color:#fff; font-size:1.3rem; font-weight:800;">Mudah</div>
            <div style="color:rgba(255,255,255,.72); font-size:.7rem; margin-top:.2rem;">Langkah Sederhana</div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== PANEL KANAN (form) ===== --}}
    <div style="flex:0 0 auto; width:100%; max-width:480px; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; background:#fff; overflow-y:auto;">

      {{-- Mobile logo --}}
      <div class="lg:hidden" style="text-align:center; margin-bottom:1.25rem;">
        <img src="{{ asset('assets/logologin.png') }}" alt="Logo" style="height:58px; object-fit:contain;">
      </div>

      <div class="fade-up" style="width:100%; max-width:380px;">

        {{-- Heading --}}
        <div style="margin-bottom:1.5rem;">
          <h2 style="font-size:1.4rem; font-weight:800; color:#3b2209; margin:0 0 .3rem;">Lupa Password?</h2>
          <p style="font-size:.82rem; color:#a77d52; margin:0;">Masukkan email untuk menerima tautan reset</p>
        </div>

        {{-- Success --}}
        @if (session('status'))
          <div class="alert-success">
            <div style="font-weight:700; margin-bottom:.3rem;">✓ Email terkirim</div>
            <div style="font-size:.75rem;">{{ session('status') }} Periksa folder Spam jika tidak ditemukan.</div>
          </div>
        @endif

        {{-- Error --}}
        @if ($errors->any())
          <div style="padding:.75rem 1rem; border-radius:.625rem; background:#fff5f5; border:1.5px solid rgba(220,38,38,.25); font-size:.82rem; margin-bottom:1.25rem;">
            <div style="font-weight:700; color:#b91c1c; margin-bottom:.4rem;">Gagal mengirim email</div>
            <ul style="margin:0; padding-left:1.2rem; color:#b91c1c;">
              @foreach ($errors->all() as $error)
                <li style="margin:.2rem 0;">{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('password.email') }}" style="display:flex; flex-direction:column; gap:1.25rem;">
          @csrf

          <div>
            <label class="auth-label">Alamat Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="auth-input"
              placeholder="email@contoh.com" required autocomplete="email" autofocus>
            @error('email')
              <div class="field-error">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <button type="submit" class="auth-btn">
              Kirim Tautan Reset
            </button>
          </div>
        </form>

        {{-- Links --}}
        <p style="text-align:center; margin-top:1.25rem; font-size:.82rem; color:#9c7a52;">
          Ingat password Anda?
          <a href="{{ route('login') }}"
            style="color:#a77d52; font-weight:700; text-decoration:none;"
            onmouseover="this.style.textDecoration='underline'"
            onmouseout="this.style.textDecoration='none'">
            Masuk sekarang
          </a>
        </p>

        <p style="text-align:center; margin-top:1rem; font-size:.82rem; color:#9c7a52;">
          Belum punya akun?
          <a href="{{ route('register') }}"
            style="color:#a77d52; font-weight:700; text-decoration:none;"
            onmouseover="this.style.textDecoration='underline'"
            onmouseout="this.style.textDecoration='none'">
            Daftar gratis
          </a>
        </p>

        <p style="text-align:center; margin-top:1.75rem; font-size:.72rem; color:#c4a882;">
          © {{ now()->year }} PT Andalan Artha Primanusa
        </p>

      </div>
    </div>

  </div>

</body>
</html>
