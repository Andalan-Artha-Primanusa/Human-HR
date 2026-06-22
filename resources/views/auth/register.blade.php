<!DOCTYPE html>
<html lang="id">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Akun • Human.Careers</title>
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
    .terms-modal {
      position: fixed;
      inset: 0;
      z-index: 50;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      background: rgba(0,0,0,.56);
    }
    .terms-modal.is-open { display: flex; }
    .terms-dialog {
      width: min(720px, 100%);
      max-height: min(760px, 90vh);
      overflow: hidden;
      border-radius: 1rem;
      background: #fff;
      box-shadow: 0 24px 80px rgba(0,0,0,.28);
      border: 1px solid rgba(167,125,82,.22);
    }
    .terms-scroll {
      max-height: 54vh;
      overflow-y: auto;
      padding-right: .25rem;
    }
    .terms-close {
      border: 0;
      background: #fff8f2;
      color: #5c3d1e;
      border-radius: .625rem;
      padding: .45rem .65rem;
      font-weight: 800;
      cursor: pointer;
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
          Bergabung Bersama<br>Tim Profesional Andalan
        </h1>
        <p style="color:rgba(255,255,255,.8); font-size:.9rem; line-height:1.75; margin:0 0 2rem;">
          Daftarkan diri sekarang dan mulai perjalanan karier Anda bersama PT Andalan Artha Primanusa.
        </p>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.6rem; margin-top:1rem;">
          <div style="background:rgba(255,255,255,.12); border-radius:.875rem; padding:.85rem .5rem; text-align:center;">
            <div style="color:#fff; font-size:1.3rem; font-weight:800;">Gratis</div>
            <div style="color:rgba(255,255,255,.72); font-size:.7rem; margin-top:.2rem;">Proses Daftar</div>
          </div>
          <div style="background:rgba(255,255,255,.12); border-radius:.875rem; padding:.85rem .5rem; text-align:center;">
            <div style="color:#fff; font-size:1.3rem; font-weight:800;">Cepat</div>
            <div style="color:rgba(255,255,255,.72); font-size:.7rem; margin-top:.2rem;">Proses Seleksi</div>
          </div>
          <div style="background:rgba(255,255,255,.12); border-radius:.875rem; padding:.85rem .5rem; text-align:center;">
            <div style="color:#fff; font-size:1.3rem; font-weight:800;">Jelas</div>
            <div style="color:rgba(255,255,255,.72); font-size:.7rem; margin-top:.2rem;">Status Lamaran</div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== PANEL KANAN (form) ===== --}}
    <div style="flex:0 0 auto; width:100%; max-width:480px; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; background:#fff; overflow-y:auto;">

      {{-- Mobile logo --}}
      <div class="lg:hidden" style="text-align:center; margin-bottom:1.25rem;">
        <img src="{{ asset('assets/logologin.png') }}" alt="Logo" style="height:80px; object-fit:contain; display:block; margin:0 auto;">
      </div>

      <div class="fade-up" style="width:100%; max-width:380px;">

        {{-- Heading --}}
        <div style="margin-bottom:1.5rem; text-align:center;">
          <div class="hidden lg:block" style="margin-bottom:.6rem;">
            <img src="{{ asset('assets/logologin.png') }}" alt="Logo" style="height:80px; object-fit:contain; display:block; margin:0 auto;">
          </div>
          <h2 style="font-size:1.4rem; font-weight:800; color:#3b2209; margin:0 0 .3rem;">Buat Akun Baru</h2>
          <p style="font-size:.82rem; color:#a77d52; margin:0;">Isi data di bawah untuk mendaftar</p>
        </div>

        {{-- Error --}}
        @if ($errors->any())
          <div style="padding:.75rem 1rem; border-radius:.625rem; background:#fff5f5; border:1.5px solid rgba(220,38,38,.25); font-size:.82rem; margin-bottom:1.25rem;">
            <div style="font-weight:700; color:#b91c1c; margin-bottom:.4rem;">Gagal mendaftar</div>
            <ul style="margin:0; padding-left:1.2rem; color:#b91c1c; space-y:.2rem;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('register') }}" class="space-y-3">
          @csrf

          <div>
            <label class="auth-label">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" class="auth-input"
              placeholder="Nama lengkap Anda" required autocomplete="name" autofocus>
            @error('name')
              <div class="field-error">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label class="auth-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="auth-input"
              placeholder="email@contoh.com" required autocomplete="email">
            @error('email')
              <div class="field-error">{{ $message }}</div>
            @enderror
          </div>

          <div style="position:relative; margin-bottom:.5rem;">
            <label class="auth-label">Password</label>
            <div style="position:relative;">
              <input id="register_password" type="password" name="password" class="auth-input"
                placeholder="Minimal 8 karakter" required autocomplete="new-password" style="padding-right:3.5rem;">
              <button type="button" id="toggleRegisterPassword" aria-label="Tampilkan password"
                style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:transparent; border:none; cursor:pointer; padding:.25rem; color:#a77d52;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" xmlns="http://www.w3.org/2000/svg">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
            @error('password')
              <div class="field-error">{{ $message }}</div>
            @enderror
          </div>

          <div style="position:relative;">
            <label class="auth-label">Konfirmasi Password</label>
            <div style="position:relative;">
              <input id="register_password_confirmation" type="password" name="password_confirmation" class="auth-input"
                placeholder="Ulangi password" required autocomplete="new-password" style="padding-right:3.5rem;">
              <button type="button" id="toggleRegisterPasswordConfirm" aria-label="Tampilkan konfirmasi password"
                style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:transparent; border:none; cursor:pointer; padding:.25rem; color:#a77d52;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" xmlns="http://www.w3.org/2000/svg">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          {{-- Terms --}}
          <div style="background:#fff8f2; border-radius:.75rem; border:1.5px solid rgba(167,125,82,.2); padding:.85rem 1rem;">
            <label for="agree" style="display:flex; align-items:flex-start; gap:.65rem; font-size:.8rem; color:#5c3d1e; cursor:pointer; line-height:1.55;">
              <input id="agree" type="checkbox" name="agree" value="1"
                style="width:15px; height:15px; margin-top:2px; accent-color:#a77d52; flex-shrink:0; cursor:pointer;"
                {{ old('agree') ? 'checked' : '' }} required>
              <span>
                Saya telah membaca dan menyetujui
                <button type="button" id="openTermsModal"
                  style="border:0; background:transparent; padding:0; color:#a77d52; font:inherit; font-weight:700; text-decoration:underline; cursor:pointer;">
                  Terms &amp; Conditions
                </button>
                Human.Careers.
              </span>
            </label>
            @error('agree')
              <div class="field-error" style="margin-top:.4rem;">{{ $message }}</div>
            @enderror
          </div>

          <div style="padding-top:.25rem;">
            <button id="registerSubmit" type="submit" class="auth-btn" disabled>
              Buat Akun
            </button>
          </div>
        </form>

        {{-- Login link --}}
        <p style="text-align:center; margin-top:1.25rem; font-size:.82rem; color:#9c7a52;">
          Sudah punya akun?
          <a href="{{ route('login') }}"
            style="color:#a77d52; font-weight:700; text-decoration:none;"
            onmouseover="this.style.textDecoration='underline'"
            onmouseout="this.style.textDecoration='none'">
            Masuk sekarang
          </a>
        </p>

        <p style="text-align:center; margin-top:1.75rem; font-size:.72rem; color:#c4a882;">
          © {{ now()->year }} PT Andalan Artha Primanusa
        </p>

      </div>
    </div>

  </div>

  <div id="termsModal" class="terms-modal" role="dialog" aria-modal="true" aria-labelledby="termsModalTitle">
    <div class="terms-dialog">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1rem 1.25rem; border-bottom:1px solid rgba(167,125,82,.18);">
        <div>
          <h3 id="termsModalTitle" style="margin:0; color:#3b2209; font-size:1rem; font-weight:800;">Terms &amp; Conditions</h3>
          <p style="margin:.2rem 0 0; color:#9c7a52; font-size:.75rem;">Baca sebelum membuat akun Human.Careers</p>
        </div>
        <button type="button" id="closeTermsModal" class="terms-close" aria-label="Tutup Terms and Conditions">Tutup</button>
      </div>

      <div style="padding:1.25rem;">
        <div class="terms-scroll" style="color:#5c3d1e; font-size:.84rem; line-height:1.75;">
          <p style="margin-top:0;">Dengan membuat akun, Anda setuju menggunakan Human.Careers untuk proses rekrutmen, pengelolaan profil kandidat, lamaran pekerjaan, psikotes, interview, dan komunikasi seleksi.</p>

          <h4 style="margin:1rem 0 .35rem; color:#3b2209;">1. Kebenaran Data</h4>
          <p>Anda wajib mengisi data yang benar, lengkap, dan terbaru. Data yang tidak valid dapat memengaruhi proses seleksi atau menyebabkan akun dibatasi.</p>

          <h4 style="margin:1rem 0 .35rem; color:#3b2209;">2. Verifikasi Email</h4>
          <p>Setelah registrasi, Anda wajib melakukan verifikasi email melalui link yang dikirim sistem sebelum mengakses fitur tertentu.</p>

          <h4 style="margin:1rem 0 .35rem; color:#3b2209;">3. Keamanan Akun</h4>
          <p>Anda bertanggung jawab menjaga kerahasiaan password dan aktivitas yang terjadi melalui akun Anda.</p>

          <h4 style="margin:1rem 0 .35rem; color:#3b2209;">4. Penggunaan Data</h4>
          <p>Data digunakan untuk kebutuhan rekrutmen, evaluasi kandidat, komunikasi proses seleksi, dan administrasi internal perusahaan.</p>

          <h4 style="margin:1rem 0 .35rem; color:#3b2209;">5. Perubahan Ketentuan</h4>
          <p>Ketentuan dapat diperbarui sewaktu-waktu. Penggunaan platform setelah perubahan berlaku dianggap sebagai persetujuan terhadap ketentuan terbaru.</p>
        </div>

        <div style="display:flex; gap:.75rem; justify-content:flex-end; margin-top:1rem; padding-top:1rem; border-top:1px solid rgba(167,125,82,.14);">
          <a href="{{ route('terms') }}" target="_blank" rel="noopener" style="align-self:center; color:#9c7a52; font-size:.8rem; font-weight:700; text-decoration:underline;">Buka halaman penuh</a>
          <button type="button" id="acceptTermsFromModal" class="auth-btn" style="width:auto; min-width:150px; padding:.7rem 1rem;">Saya Setuju</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function () {
      const agree  = document.getElementById('agree');
      const submit = document.getElementById('registerSubmit');
      function sync() { if (submit) submit.disabled = !agree?.checked; }
      agree?.addEventListener('change', sync);
      sync();

      // Password toggle for register form
      const pwd = document.getElementById('register_password');
      const pwdConfirm = document.getElementById('register_password_confirmation');
      const togglePwd = document.getElementById('toggleRegisterPassword');
      const togglePwdConfirm = document.getElementById('toggleRegisterPasswordConfirm');

      function toggleField(field, btn, hideLabel, showLabel) {
        if (!field || !btn) return;
        btn.addEventListener('click', function(){
          if (field.type === 'password') {
            field.type = 'text';
            btn.setAttribute('aria-label', hideLabel);
          } else {
            field.type = 'password';
            btn.setAttribute('aria-label', showLabel);
          }
        });
      }

      toggleField(pwd, togglePwd, 'Sembunyikan password', 'Tampilkan password');
      toggleField(pwdConfirm, togglePwdConfirm, 'Sembunyikan konfirmasi password', 'Tampilkan konfirmasi password');

      const termsModal = document.getElementById('termsModal');
      const openTerms = document.getElementById('openTermsModal');
      const closeTerms = document.getElementById('closeTermsModal');
      const acceptTerms = document.getElementById('acceptTermsFromModal');

      function openTermsModal() {
        termsModal?.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      }

      function closeTermsModal() {
        termsModal?.classList.remove('is-open');
        document.body.style.overflow = '';
      }

      openTerms?.addEventListener('click', openTermsModal);
      closeTerms?.addEventListener('click', closeTermsModal);
      termsModal?.addEventListener('click', function (event) {
        if (event.target === termsModal) closeTermsModal();
      });
      acceptTerms?.addEventListener('click', function () {
        if (agree) {
          agree.checked = true;
          sync();
        }
        closeTermsModal();
      });
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeTermsModal();
      });
    })();
  </script>
</body>
</html>
