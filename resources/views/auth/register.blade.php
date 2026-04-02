{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Akun • Human.Careers</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="min-h-screen text-slate-800">

<div class="min-h-screen relative overflow-hidden">

  <!-- BACKGROUND -->
  <img src="{{ asset('assets/hr1.jpg') }}"
       class="absolute inset-0 w-full h-full object-cover"
       alt="bg">


  <!-- TEXT KIRI -->
  <div class="hidden lg:flex absolute left-0 top-0 h-full items-center pl-20 text-white z-10">
    <div class="max-w-md">
      <h1 class="text-4xl font-bold mb-4">Human Careers</h1>
      <p class="text-white/80">
        Platform rekrutmen resmi Andalan Group untuk membantu Anda menemukan karier terbaik.
      </p>
    </div>
  </div>

  <!-- FORM -->
  <div class="absolute inset-0 flex items-center justify-end pr-6 sm:pr-14 lg:pr-28 z-10">

    <!-- 🔥 LEBAR DIKECILIN -->
    <div class="w-full max-w-sm">

      <!-- ERROR -->
      @if ($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
          Ada beberapa isian yang perlu diperbaiki.
        </div>
      @endif

      <!-- CARD -->
      <div class="rounded-2xl shadow-2xl p-8 border border-white/10"
            style="background-color: rgba(167, 125, 82, 0.9);"
            style="background-color: #a77d52;">

        <div class="flex flex-col items-center mb-6 space-y-3">

              <!-- LOGO ATAS -->
              <img src="{{ asset('assets/logoicon.png') }}"
                alt="Logo Perusahaan"
                class="h-16 md:h-20 object-contain">

              <!-- GAMBAR YANG SUDAH ADA -->
              <img src="{{ asset('assets/foto2.png') }}"
                alt="Human Resource Andalan"
                class="h-16 md:h-20 object-contain">

            </div>

        <!-- FORM -->
        <form id="registerForm" method="POST" action="{{ route('register') }}" class="space-y-4">
          @csrf

          <!-- NAMA -->
          <div>
            <label class="text-sm text-white/90">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}"
              class="w-full mt-1 px-3 py-2.5 rounded-lg bg-white/90 text-gray-800 border border-white/40 focus:ring-2 focus:ring-white focus:outline-none"
              placeholder="Nama sesuai KTP">
          </div>

          <!-- EMAIL -->
          <div>
            <label class="text-sm text-white/90">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
              class="w-full mt-1 px-3 py-2.5 rounded-lg bg-white/90 text-gray-800 border border-white/40 focus:ring-2 focus:ring-white focus:outline-none"
              placeholder="nama@email.com">
          </div>

          <!-- PASSWORD -->
          <div class="relative">
            <label class="text-sm text-white/90">Password</label>
            <input type="password" name="password"
              class="w-full mt-1 px-3 py-2.5 pr-10 rounded-lg bg-white/90 text-gray-800 border border-white/40 focus:ring-2 focus:ring-white focus:outline-none"
              placeholder="Minimal 8 karakter">
          </div>

          <!-- KONFIRMASI -->
          <div class="relative">
            <label class="text-sm text-white/90">Konfirmasi Password</label>
            <input type="password" name="password_confirmation"
              class="w-full mt-1 px-3 py-2.5 pr-10 rounded-lg bg-white/90 text-gray-800 border border-white/40 focus:ring-2 focus:ring-white focus:outline-none"
              placeholder="Ulangi password">
          </div>

          <!-- AGREEMENT -->
          <div class="flex items-start gap-2 text-xs text-white/90">
            <input type="checkbox" id="agree" class="mt-1 rounded">
            <label for="agree">Saya menyetujui Ketentuan & Privasi</label>
          </div>

          <!-- BUTTON -->
          <button type="submit"
            class="w-full bg-white text-[#a77d52] py-2.5 rounded-lg font-semibold hover:opacity-90 transition">
            Buat Akun
          </button>

        </form>

      </div>

      <!-- LOGIN -->
      <div class="mt-5 text-center text-sm text-white/80">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="underline">Masuk</a>
      </div>

      <!-- FOOTER -->
      <div class="mt-6 text-center text-xs text-white/60">
        © {{ now()->year }} Andalan Group
      </div>

    </div>
  </div>

</div>

<!-- ================= JS ================= -->
<script>
(function () {

  const pw = document.querySelector('input[name="password"]');
  const pc = document.querySelector('input[name="password_confirmation"]');
  const form = document.getElementById('registerForm');
  const btn = document.querySelector('button[type="submit"]');
  const agree = document.getElementById('agree');

  // TOGGLE PASSWORD
  function createToggle(input) {
    const wrapper = input.parentElement;

    const btnToggle = document.createElement('button');
    btnToggle.type = 'button';
    btnToggle.innerHTML = '👁';
    btnToggle.className = "absolute right-3 top-1/2 -translate-y-1/2 text-gray-600";

    wrapper.appendChild(btnToggle);

    btnToggle.addEventListener('click', () => {
      input.type = input.type === 'password' ? 'text' : 'password';
    });
  }

  if (pw) createToggle(pw);
  if (pc) createToggle(pc);

  // PASSWORD STRENGTH
  const bar = document.createElement('div');
  bar.className = "h-1 w-full bg-white/30 rounded mt-2 overflow-hidden";

  const fill = document.createElement('div');
  fill.className = "h-full w-0 bg-white transition-all";

  bar.appendChild(fill);
  pw.parentElement.appendChild(bar);

  function scorePassword(s) {
    let score = 0;
    if (!s) return 0;
    if (s.length >= 8) score += 20;
    if (/[A-Z]/.test(s)) score += 20;
    if (/[a-z]/.test(s)) score += 20;
    if (/[0-9]/.test(s)) score += 20;
    if (/[^A-Za-z0-9]/.test(s)) score += 20;
    return score;
  }

  pw?.addEventListener('input', () => {
    fill.style.width = scorePassword(pw.value) + "%";
  });

  // SUBMIT
  form?.addEventListener('submit', function(e) {
    if (!agree.checked) {
      e.preventDefault();
      alert("Centang persetujuan dulu");
      return;
    }

    btn.disabled = true;
    btn.innerText = "Memproses...";
  });

})();
</script>

</body>
</html>