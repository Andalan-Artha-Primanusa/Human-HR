{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk • Human.Careers</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; }
    html, body {
      margin: 0; padding: 0; height: 100%;
      font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
      -webkit-font-smoothing: antialiased;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0) }
      20%       { transform: translateX(-5px) }
      40%       { transform: translateX(5px) }
      60%       { transform: translateX(-3px) }
      80%       { transform: translateX(3px) }
    }
    .shake { animation: shake .4s ease-in-out 1; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp .45s ease both; }

    .auth-input {
      width: 100%;
      padding: .75rem 1rem;
      border-radius: .625rem;
      border: 1.5px solid rgba(167,125,82,.25);
      background: #fff;
      color: #3b2209;
      font-size: .9rem;
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
      font-size: .8rem;
      font-weight: 600;
      color: #5c3d1e;
      margin-bottom: .35rem;
      letter-spacing: .02em;
    }

    .auth-btn {
      width: 100%;
      padding: .85rem;
      background: #a77d52;
      color: #fff;
      border: none;
      border-radius: .625rem;
      font-size: .95rem;
      font-weight: 700;
      cursor: pointer;
      transition: opacity .2s, transform .15s, box-shadow .2s;
      box-shadow: 0 4px 14px rgba(167,125,82,.35);
      letter-spacing: .02em;
    }
    .auth-btn:hover  { opacity: .92; box-shadow: 0 6px 20px rgba(167,125,82,.45); }
    .auth-btn:active { transform: scale(.98); }
  </style>
</head>

<body style="background:#f9f3ee; min-height:100vh; display:flex; align-items:stretch;">

  <div style="display:flex; width:100%; min-height:100vh;">

    {{-- ===== PANEL KIRI (hidden mobile) ===== --}}
    <div class="hidden lg:flex" style="flex:1; position:relative; overflow:hidden; flex-direction:column; align-items:center; justify-content:center; padding:3rem;">
      {{-- Background foto --}}
      <img src="{{ asset('assets/hr1.jpg') }}" alt="" aria-hidden="true"
        style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; object-position:center;">
      {{-- Overlay #a77d52 --}}
      <div style="position:absolute; inset:0; background:rgba(167,125,82,0.82);"></div>
      {{-- Decorative circles --}}
      <div style="position:absolute; top:-80px; right:-80px; width:320px; height:320px; border-radius:50%; background:rgba(255,255,255,.08);"></div>
      <div style="position:absolute; bottom:-60px; left:-60px; width:240px; height:240px; border-radius:50%; background:rgba(255,255,255,.06);"></div>
      <div style="position:absolute; top:40%; left:-30px; width:140px; height:140px; border-radius:50%; background:rgba(255,255,255,.05);"></div>

      <div style="position:relative; z-index:1; text-align:center; max-width:400px;">
        <h1 style="color:#fff; font-size:2rem; font-weight:800; margin:0 0 1rem; line-height:1.2;">
          Selamat Datang<br>di Human.Careers
        </h1>
        <p style="color:rgba(255,255,255,.8); font-size:.95rem; line-height:1.7; margin:0 0 2rem;">
          Platform rekrutmen resmi PT Andalan Artha Primanusa. Temukan karier terbaik Anda bersama kami.
        </p>

        {{-- Stats --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-top:1.5rem;">
          <div style="background:rgba(255,255,255,.12); border-radius:.875rem; padding:1rem; text-align:center;">
            <div style="color:#fff; font-size:1.6rem; font-weight:800;">100%</div>
            <div style="color:rgba(255,255,255,.75); font-size:.75rem; margin-top:.2rem;">Rekrutmen Gratis</div>
          </div>
          <div style="background:rgba(255,255,255,.12); border-radius:.875rem; padding:1rem; text-align:center;">
            <div style="color:#fff; font-size:1.6rem; font-weight:800;">Real‑time</div>
            <div style="color:rgba(255,255,255,.75); font-size:.75rem; margin-top:.2rem;">Status Lamaran</div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== PANEL KANAN (form) ===== --}}
    <div style="flex:0 0 auto; width:100%; max-width:480px; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; background:#fff;">

      {{-- Mobile logo --}}
      <div class="lg:hidden" style="text-align:center; margin-bottom:1.5rem;">
        <img src="{{ asset('assets/logologin.png') }}" alt="Logo" style="height:64px; object-fit:contain;">
      </div>

      <div class="fade-up {{ $errors->first('email') ? 'shake' : '' }}" style="width:100%; max-width:380px;">

        {{-- Heading --}}
        <div style="margin-bottom:1.75rem;">
          <h2 style="font-size:1.5rem; font-weight:800; color:#3b2209; margin:0 0 .35rem;">Masuk ke Akun</h2>
          <p style="font-size:.85rem; color:#a77d52; margin:0;">Silakan isi email dan password Anda</p>
        </div>

        {{-- Error --}}
        @if ($errors->first('email'))
          <div style="padding:.75rem 1rem; border-radius:.625rem; background:#fff5f5; border:1.5px solid rgba(220,38,38,.25); color:#b91c1c; font-size:.85rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ $errors->first('email') }}
          </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}">
          @csrf

          <div style="margin-bottom:1rem;">
            <label class="auth-label">Email</label>
            <input type="email" name="email" class="auth-input"
              placeholder="nama@perusahaan.com"
              value="{{ old('email') }}" autocomplete="email" autofocus>
          </div>

          <div style="margin-bottom:.75rem;">
            <label class="auth-label">Password</label>
            <input type="password" name="password" class="auth-input"
              placeholder="••••••••" autocomplete="current-password">
          </div>

          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; font-size:.82rem;">
            <label style="display:flex; align-items:center; gap:.5rem; color:#5c3d1e; cursor:pointer;">
              <input type="checkbox" name="remember"
                style="width:15px; height:15px; accent-color:#a77d52; cursor:pointer;">
              Ingat saya
            </label>
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}"
                style="color:#a77d52; text-decoration:none; font-weight:600;"
                onmouseover="this.style.textDecoration='underline'"
                onmouseout="this.style.textDecoration='none'">
                Lupa password?
              </a>
            @endif
          </div>

          <button type="submit" class="auth-btn">Masuk</button>
        </form>

        {{-- Register link --}}
        @if (Route::has('register'))
          <p style="text-align:center; margin-top:1.5rem; font-size:.85rem; color:#9c7a52;">
            Belum punya akun?
            <a href="{{ route('register') }}"
              style="color:#a77d52; font-weight:700; text-decoration:none;"
              onmouseover="this.style.textDecoration='underline'"
              onmouseout="this.style.textDecoration='none'">
              Daftar Gratis
            </a>
          </p>
        @endif

        <p style="text-align:center; margin-top:2rem; font-size:.75rem; color:#c4a882;">
          © {{ now()->year }} PT Andalan Artha Primanusa
        </p>

      </div>
    </div>

  </div>

</body>
</html>
