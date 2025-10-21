{{-- resources/views/layouts/partials/sidenav.blade.php --}}
@php
  use Illuminate\Support\Str;

  // ===== Props =====
  $variant      = $variant      ?? 'desktop'; // 'desktop' | 'mobile'
  $closeOnClick = $closeOnClick ?? false;     // only used on mobile
  $closeAttr    = $closeOnClick ? ' @click="open=false"' : '';
  $offerQuickId = $offerQuickId ?? null;
  $logoUrl      = $logoUrl      ?? asset('assets/logo-andalan.svg'); // ganti jika perlu
  $appName      = config('app.name', 'Careers Portal');

  // ===== Auth context =====
  /** @var \App\Models\User|null $u */
  $u = auth()->user();
  $roleRaw = $u->role ?? 'pelamar';
  $roleMap = [
    'superadmin' => 'Super Admin',
    'admin'      => 'Admin',
    'hr'         => 'HR',
    'pelamar'    => 'Pelamar',
  ];
  $roleName = $roleMap[$roleRaw] ?? Str::title($roleRaw);

  // ====== Verified check (Laravel default + fallback) ======
  $isVerified = false;
  if ($u) {
    if (method_exists($u, 'hasVerifiedEmail')) {
      $isVerified = $u->hasVerifiedEmail();
    } elseif (isset($u->email_verified_at) && $u->email_verified_at) {
      $isVerified = true;
    } elseif (isset($u->verified) && $u->verified) {
      $isVerified = true;
    }
  }

  // Avatar (initials) fallback
  $initials = '';
  if ($u && $u->name) {
      $parts = preg_split('/\s+/', trim($u->name));
      $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
  }

  // ===== Active state helpers (tebal kiri + kontras teks) =====
  $activeBlue = fn($p) => request()->routeIs($p)
      ? 'border-blue-600 bg-blue-50 text-slate-900 font-semibold ring-1 ring-black/5'
      : 'border-transparent text-blue-800';

  $activeRed = fn($p) => request()->routeIs($p)
      ? 'border-red-600 bg-red-50 text-slate-900 font-semibold ring-1 ring-black/5'
      : 'border-transparent text-red-800';

  // ===== Link base styles (tanpa gradient, solid, ada border-l indikator) =====
  $baseDesk   = 'flex items-center gap-3 px-3 py-2 rounded-lg border-l-4 transition hover:bg-slate-50 hover:ring-1 hover:ring-black/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/20';
  $baseMobile = 'flex items-center gap-3 px-3 py-2 rounded-lg border-l-4 transition hover:bg-slate-50 hover:ring-1 hover:ring-black/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/20';

  // Warna label default (non-active)
  $linkDeskBlue   = $baseDesk;
  $linkDeskRed    = $baseDesk;
  $linkMobileBlue = $baseMobile;
  $linkMobileRed  = $baseMobile;

  // Komponen kecil: chip ikon solid tanpa gradient
  $iconBlue = 'inline-grid place-content-center w-7 h-7 rounded-md bg-blue-100 text-blue-700 ring-1 ring-black/5';
  $iconRed  = 'inline-grid place-content-center w-7 h-7 rounded-md bg-red-100 text-red-700 ring-1 ring-black/5';

  // Section header dengan aksen kecil hitam
  $sectionTitle = 'px-3 pt-4 pb-1 text-[11px] tracking-wide font-semibold uppercase text-slate-700';

  // ===== Lock styling kalau belum verified =====
  $lockClass = !$isVerified ? 'opacity-60 pointer-events-none select-none' : '';
@endphp

{{-- ====== MINI MODE FIX khusus sidenav (responsive account/login/logout) ====== --}}
<style>
@media (min-width: 768px){
  /* Account card jadi icon-only saat sidebar mini */
  aside.is-mini a.account-card{ justify-content:center; padding:.5rem .5rem !important; }
  aside.is-mini a.account-card .account-info{ display:none !important; }
  /* Hint "Belum masuk (login)" disembunyikan saat mini */
  aside.is-mini .login-hint{ display:none !important; }
  /* Logout: gunakan .btn dari layout -> pastikan ada class .btn di button */
  aside.is-mini form .btn{
    width:44px;height:44px;padding:0;border-radius:.75rem;
    display:flex;align-items:center;justify-content:center;
  }
  aside.is-mini form .btn > span{ gap:0; }
  aside.is-mini form .btn .label{ display:none; }
}
</style>

@if($variant === 'desktop')
<nav class="p-3 space-y-1 text-sm text-slate-900 flex flex-col min-h-full">
  {{-- ====== HEADER: LOGO ====== --}}
  <div class="mb-3">
    <a href="{{ url('/') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 hover:ring-1 hover:ring-black/5">
      <img src="{{ $logoUrl }}" alt="Logo" class="w-8 h-8 object-contain" onerror="this.style.display='none'">
      <div class="leading-tight">
        <div class="font-semibold text-slate-900">{{ $appName }}</div>
        <div class="text-[10px] text-slate-500">Andalan Group</div>
      </div>
    </a>
  </div>

  {{-- ====== ACCOUNT (PALING ATAS) ====== --}}
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-slate-900 align-middle mr-2"></span>
    <span class="align-middle text-slate-900">Account</span>
  </div>

  @auth
    <a href="{{ route('profile.edit') }}" class="account-card mx-3 mb-2 block rounded-xl border border-slate-200 hover:bg-slate-50 hover:ring-1 hover:ring-black/5 transition">
      <div class="flex items-center gap-3 px-3 py-2">
        @if($u && property_exists($u,'profile_photo_url') && $u->profile_photo_url)
          <img src="{{ $u->profile_photo_url }}" alt="{{ $u->name }}" class="w-9 h-9 rounded-full object-cover ring-1 ring-black/5">
        @else
          <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 grid place-content-center font-semibold ring-1 ring-black/5">
            {{ $u ? $initials : 'G' }}
          </div>
        @endif
        <div class="account-info min-w-0">
          <div class="text-xs text-slate-600 truncate max-w-[220px]">{{ $u->email }}</div>
          <div class="font-medium text-slate-900 truncate max-w-[180px]">{{ $u->name }}</div>
          <div class="mt-0.5 inline-flex items-center text-[10px] px-2 py-0.5 rounded-full
                      {{ $isVerified ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }} ring-1 ring-black/5">
            {{ $isVerified ? 'Verified' : 'Belum Terverifikasi' }}
          </div>
        </div>
      </div>
    </a>

    {{-- Banner aksi verifikasi --}}
    @if(!$isVerified)
      <div class="mx-3 mb-2 rounded-lg border border-red-200 bg-red-50 p-3">
        <div class="text-[12px] text-red-800 mb-2">
          Akun kamu belum terverifikasi. Harus verifikasi dulu untuk akses menu.
        </div>
        @if (Route::has('verification.send'))
          <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="inline-flex items-center gap-2 rounded-md bg-red-600 text-white px-3 py-1.5 text-xs font-semibold hover:bg-red-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m0 0l3-3m-3 3 3 3M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
              </svg>
              Harus verifikasi
            </button>
          </form>
        @else
          <a href="{{ route('verification.notice') }}" class="inline-flex items-center gap-2 rounded-md bg-red-600 text-white px-3 py-1.5 text-xs font-semibold hover:bg-red-700">
            Harus verifikasi
          </a>
        @endif
      </div>
    @endif
  @else
    <div class="login-hint px-3 text-xs text-slate-600 mb-1">Belum masuk (login)</div>
    <a href="{{ route('login') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('login') }} mx-3">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15V18.75A2.25 2.25 0 0 0 10.5 21h6.75A2.25 2.25 0 0 0 19.5 18.75v-13.5A2 2 0 0 0 17.25 3H10.5A2.25 2.25 0 0 0 8.25 5.25V9M15 12H3m0 0 3-3m-3 3 3 3" />
        </svg>
      </span>
      <span class="label">Login</span>
    </a>
  @endauth

  {{-- ====== GENERAL ====== --}}
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-blue-700 align-middle mr-2"></span>
    <span class="align-middle text-blue-700">General</span>
  </div>

  <div class="{{ $lockClass }}">
    <a href="{{ route('jobs.index') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('jobs.*') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z" />
        </svg>
      </span>
      <span class="label">Lowongan</span>
    </a>

    @auth
    <a href="{{ route('dashboard') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('dashboard') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h6.5v6.5h-6.5zM13.75 4.5h6.5v6.5h-6.5zM3.75 14.5h6.5v6.5h-6.5zM13.75 14.5h6.5v6.5h-6.5z" />
        </svg>
      </span>
      <span class="label">Dashboard</span>
    </a>

    <a href="{{ route('applications.mine') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('applications.mine') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 0 0-2 2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2Z" />
        </svg>
      </span>
      <span class="label">Lamaran Saya</span>
    </a>
    @endauth
  </div>

  {{-- Divider --}}
  <div class="my-2 border-t border-slate-200"></div>

  {{-- ====== ADMIN ====== --}}
  @auth
  @if(in_array($roleRaw, ['hr','superadmin','admin']))
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-red-700 align-middle mr-2"></span>
    <span class="align-middle text-red-700">Admin</span>
  </div>

  <div class="{{ $lockClass }}">
    @if (Route::has('admin.sites.index'))
    <a href="{{ route('admin.sites.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.sites.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V5a2 2 0 0 1 2-2h5v18m7 0V9a2 2 0 0 0-2-2h-5" />
        </svg>
      </span>
      <span class="label">Sites</span>
    </a>
    @endif

    <a href="{{ route('admin.jobs.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.jobs.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z" />
        </svg>
      </span>
      <span class="label">Jobs</span>
    </a>

    @if (Route::has('admin.candidates.index'))
    <a href="{{ route('admin.candidates.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.candidates.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19a4 4 0 1 0-6 0M12 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6 12v-1a4 4 0 0 0-4-4H10a4 4 0 0 0-4 4v1" />
        </svg>
      </span>
      <span class="label">Candidates</span>
    </a>
    @endif

    <a href="{{ route('admin.applications.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.applications.index') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 0 0-2 2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2Z" />
        </svg>
      </span>
      <span class="label">Applications</span>
    </a>

    <a href="{{ route('admin.applications.board') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.applications.board') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z" />
        </svg>
      </span>
      <span class="label">Kanban Board</span>
    </a>

    @if (Route::has('admin.interviews.index'))
    <a href="{{ route('admin.interviews.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.interviews.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 0 1 8.75 6.5h6.5A6.25 6.25 0 0 1 21.5 12.75v.25a4.75 4.75 0 0 1-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 0 1 4 13v-.25Z" />
        </svg>
      </span>
      <span class="label">Interviews</span>
    </a>
    @endif

    @if (Route::has('admin.psychotests.index'))
    <a href="{{ route('admin.psychotests.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.psychotests.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 2.5v4l-5.5 9A3 3 0 0 0 6 20.5h12a3 3 0 0 0 2.5-5l-5.5-9v-4" />
        </svg>
      </span>
      <span class="label">Psychotests</span>
    </a>
    @endif

    @if (Route::has('admin.offers.index'))
    <a href="{{ route('admin.offers.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.offers.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 0 0 4.5 4v16A2.5 2.5 0 0 0 7 22.5h10A2.5 2.5 0 0 0 19.5 20V7.5Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 13h7M8.5 16h7M8.5 10h4" />
        </svg>
      </span>
      <span class="label">Offers</span>
    </a>
    @endif

    @if ($offerQuickId && Route::has('admin.offers.pdf'))
    <a href="{{ route('admin.offers.pdf', $offerQuickId) }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.offers.pdf') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 12 15.75l3-3m-3-10.5v10.5M6 19.5h12M19.5 7.5 13.5 1.5" />
        </svg>
      </span>
      <span class="label">Offer PDF (quick)</span>
    </a>
    @endif

    @if (Route::has('admin.users.index'))
    <a href="{{ route('admin.users.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.users.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 14a4 4 0 10-8 0v2a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-2zM12 10a4 4 0 100-8 4 4 0 000 8z" />
        </svg>
      </span>
      <span class="label">Users</span>
    </a>
    @endif

    @if ($roleRaw === 'superadmin' && Route::has('admin.audit_logs.index'))
    <a href="{{ route('admin.audit_logs.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.audit_logs.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h11M9 12h11M9 17h11M5 7h.01M5 12h.01M5 17h.01" />
        </svg>
      </span>
      <span class="label">Audit Logs</span>
    </a>
    @endif

    <a href="{{ route('admin.dashboard.manpower') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.dashboard.manpower') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5h18M6 17V9m6 8V5m6 12v-6" />
        </svg>
      </span>
      <span class="label">Manpower Dashboard</span>
    </a>
  </div>
  @endif
  @endauth

  {{-- ===== Spacer agar tombol logout nempel bawah ===== --}}
  <div class="flex-1"></div>

  {{-- ====== LOGOUT (STICKY DI BAWAH) ====== --}}
  @auth
  <form method="POST" action="{{ route('logout') }}" class="px-3 pb-2 pt-2">
    @csrf
    <button class="btn w-full rounded-lg border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 px-3 py-2 font-medium shadow-sm hover:shadow transition" title="Logout">
      <span class="inline-flex items-center gap-2 justify-center w-full">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3H6.75A2.25 2.25 0 0 0 4.5 5.25v13.5A2.25 2.25 0 0 0 6.75 21H13.5a2.25 2.25 0 0 0 2.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3" />
        </svg>
        <span class="label">Logout</span>
      </span>
    </button>
  </form>
  @endauth
</nav>

@else
{{-- ===================== MOBILE ===================== --}}
<nav class="space-y-1 text-sm text-slate-900 flex flex-col min-h-full">
  {{-- ====== HEADER: LOGO ====== --}}
  <a href="{{ url('/') }}" {!! $closeAttr !!} class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 hover:ring-1 hover:ring-black/5">
    <img src="{{ $logoUrl }}" alt="Logo" class="w-8 h-8 object-contain" onerror="this.style.display='none'">
    <div class="leading-tight">
      <div class="font-semibold text-slate-900">{{ $appName }}</div>
      <div class="text-[10px] text-slate-500">Andalan Group</div>
    </div>
  </a>

  {{-- ====== ACCOUNT (PALING ATAS) ====== --}}
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-slate-900 align-middle mr-2"></span>
    <span class="align-middle text-slate-900">Account</span>
  </div>

  @auth
    <a href="{{ route('profile.edit') }}" {!! $closeAttr !!} class="account-card mx-3 mb-2 block rounded-xl border border-slate-200 hover:bg-slate-50 hover:ring-1 hover:ring-black/5 transition">
      <div class="flex items-center gap-3 px-3 py-2">
        @if($u && property_exists($u,'profile_photo_url') && $u->profile_photo_url)
          <img src="{{ $u->profile_photo_url }}" alt="{{ $u->name }}" class="w-9 h-9 rounded-full object-cover ring-1 ring-black/5">
        @else
          <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 grid place-content-center font-semibold ring-1 ring-black/5">
            {{ $u ? $initials : 'G' }}
          </div>
        @endif
        <div class="account-info min-w-0">
          <div class="text-xs text-slate-600 truncate max-w-[240px]">{{ $u->email }}</div>
          <div class="font-medium text-slate-900 truncate max-w-[180px]">{{ $u->name }}</div>
          <div class="mt-0.5 inline-flex items-center text-[10px] px-2 py-0.5 rounded-full
                      {{ $isVerified ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }} ring-1 ring-black/5">
            {{ $isVerified ? 'Verified' : 'Belum Terverifikasi' }}
          </div>
        </div>
      </div>
    </a>

    @if(!$isVerified)
      <div class="mx-3 mb-2 rounded-lg border border-red-200 bg-red-50 p-3">
        <div class="text-[12px] text-red-800 mb-2">
          Akun kamu belum terverifikasi. Harus verifikasi dulu untuk akses menu.
        </div>
        @if (Route::has('verification.send'))
          <form method="POST" action="{{ route('verification.send') }}" {!! $closeAttr !!}>
            @csrf
            <button class="inline-flex items-center gap-2 rounded-md bg-red-600 text-white px-3 py-1.5 text-xs font-semibold hover:bg-red-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m0 0l3-3m-3 3 3 3M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
              </svg>
              Harus verifikasi
            </button>
          </form>
        @else
          <a href="{{ route('verification.notice') }}" {!! $closeAttr !!} class="inline-flex items-center gap-2 rounded-md bg-red-600 text-white px-3 py-1.5 text-xs font-semibold hover:bg-red-700">
            Harus verifikasi
          </a>
        @endif
      </div>
    @endif
  @else
    <div class="login-hint px-3 text-xs text-slate-600 mb-1">Belum masuk (login)</div>
    <a href="{{ route('login') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('login') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15V18.75A2.25 2.25 0 0 0 10.5 21h6.75A2.25 2.25 0 0 0 19.5 18.75v-13.5A2 2 0 0 0 17.25 3H10.5A2.25 2.25 0 0 0 8.25 5.25V9M15 12H3m0 0 3-3m-3 3 3 3" />
        </svg>
      </span>
      <span>Login</span>
    </a>
  @endauth

  {{-- ====== GENERAL ====== --}}
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-blue-700 align-middle mr-2"></span>
    <span class="align-middle text-blue-700">General</span>
  </div>

  <div class="{{ $lockClass }}">
    <a href="{{ route('jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('jobs.*') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z" />
        </svg>
      </span>
      <span>Lowongan</span>
    </a>

    @auth
    <a href="{{ route('dashboard') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('dashboard') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h6.5v6.5h-6.5zM13.75 4.5h6.5v6.5h-6.5zM3.75 14.5h6.5v6.5h-6.5zM13.75 14.5h6.5v6.5h-6.5z" />
        </svg>
      </span>
      <span>Dashboard</span>
    </a>

    <a href="{{ route('applications.mine') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('applications.mine') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 0 0-2 2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2Z" />
        </svg>
      </span>
      <span>Lamaran Saya</span>
    </a>
    @endauth
  </div>

  {{-- Divider --}}
  <div class="my-2 border-t border-slate-200"></div>

  {{-- ====== ADMIN ====== --}}
  @auth
  @if(in_array($roleRaw, ['hr','superadmin','admin']))
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-red-700 align-middle mr-2"></span>
    <span class="align-middle text-red-700">Admin</span>
  </div>

  <div class="{{ $lockClass }}">
    @if (Route::has('admin.sites.index'))
    <a href="{{ route('admin.sites.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.sites.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V5a2 2 0 0 1 2-2h5v18m7 0V9a2 2 0 0 0-2-2h-5" />
        </svg>
      </span>
      <span>Sites</span>
    </a>
    @endif

    <a href="{{ route('admin.jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.jobs.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z" />
        </svg>
      </span>
      <span>Jobs</span>
    </a>

    @if (Route::has('admin.candidates.index'))
    <a href="{{ route('admin.candidates.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.candidates.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19a4 4 0 1 0-6 0M12 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6 12v-1a4 4 0 0 0-4-4H10a4 4 0 0 0-4 4v1" />
        </svg>
      </span>
      <span>Candidates</span>
    </a>
    @endif

    <a href="{{ route('admin.applications.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.applications.index') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 0 0-2 2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2Z" />
        </svg>
      </span>
      <span>Applications</span>
    </a>

    <a href="{{ route('admin.applications.board') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.applications.board') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z" />
        </svg>
      </span>
      <span>Kanban Board</span>
    </a>

    @if (Route::has('admin.interviews.index'))
    <a href="{{ route('admin.interviews.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.interviews.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 0 1 8.75 6.5h6.5A6.25 6.25 0 0 1 21.5 12.75v.25a4.75 4.75 0 0 1-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 0 1 4 13v-.25Z" />
        </svg>
      </span>
      <span>Interviews</span>
    </a>
    @endif

    @if (Route::has('admin.psychotests.index'))
    <a href="{{ route('admin.psychotests.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.psychotests.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 2.5v4l-5.5 9A3 3 0 0 0 6 20.5h12a3 3 0 0 0 2.5-5l-5.5-9v-4" />
        </svg>
      </span>
      <span>Psychotests</span>
    </a>
    @endif

    @if (Route::has('admin.offers.index'))
    <a href="{{ route('admin.offers.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.offers.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 0 0 4.5 4v16A2.5 2.5 0 0 0 7 22.5h10A2.5 2.5 0 0 0 19.5 20V7.5Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 13h7M8.5 16h7M8.5 10h4" />
        </svg>
      </span>
      <span>Offers</span>
    </a>
    @endif

    @if ($offerQuickId && Route::has('admin.offers.pdf'))
    <a href="{{ route('admin.offers.pdf', $offerQuickId) }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.offers.pdf') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 12 15.75l3-3m-3-10.5v10.5M6 19.5h12M19.5 7.5 13.5 1.5" />
        </svg>
      </span>
      <span>Offer PDF (quick)</span>
    </a>
    @endif

    @if (Route::has('admin.users.index'))
    <a href="{{ route('admin.users.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.users.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 14a4 4 0 10-8 0v2a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-2zM12 10a4 4 0 100-8 4 4 0 000 8z" />
        </svg>
      </span>
      <span>Users</span>
    </a>
    @endif

    @if ($roleRaw === 'superadmin' && Route::has('admin.audit_logs.index'))
    <a href="{{ route('admin.audit_logs.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.audit_logs.*') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h11M9 12h11M9 17h11M5 7h.01M5 12h.01M5 17h.01" />
        </svg>
      </span>
      <span>Audit Logs</span>
    </a>
    @endif

    <a href="{{ route('admin.dashboard.manpower') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.dashboard.manpower') }}">
      <span class="{{ $iconRed }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5h18M6 17V9m6 8V5m6 12v-6" />
        </svg>
      </span>
      <span>Manpower Dashboard</span>
    </a>
  </div>
  @endif
  @endauth

  {{-- ===== Spacer agar tombol logout nempel bawah ===== --}}
  <div class="flex-1"></div>

  {{-- ====== LOGOUT (STICKY DI BAWAH) ====== --}}
  @auth
    <form method="POST" action="{{ route('logout') }}" class="px-3 pb-2 pt-2">
      @csrf
      <button class="btn w-full rounded-lg border border-slate-200 bg-white text-slate-900 hover:bg-slate-50 px-3 py-2 font-medium shadow-sm transition" title="Logout">
        <span class="inline-flex items-center gap-2 justify-center w-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3H6.75A2.25 2.25 0 0 0 4.5 5.25v13.5A2.25 2.25 0 0 0 6.75 21H13.5a2.25 2.25 0 0 0 2.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3" />
          </svg>
          <span class="label">Logout</span>
        </span>
      </button>
    </form>
  @endauth
</nav>
@endif
