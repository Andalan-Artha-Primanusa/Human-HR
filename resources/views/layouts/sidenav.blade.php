{{-- resources/views/layouts/partials/sidenav.blade.php --}}
@php
  use Illuminate\Support\Str;

  // ===== Props =====
  $variant      = $variant      ?? 'desktop'; // 'desktop' | 'mobile'
  $closeOnClick = $closeOnClick ?? false;     // only used on mobile
  $offerQuickId = $offerQuickId ?? null;
  $logoUrl      = $logoUrl      ?? asset('assets/logo-andalan.svg');
  $appName      = config('app.name', 'Careers Portal');

  $closeAttr = $closeOnClick ? ' @click="open=false"' : '';

  // ===== Auth context =====
  /** @var \App\Models\User|null $u */
  $u       = auth()->user();
  $roleRaw = $u->role ?? 'pelamar';
  $roleMap = ['superadmin'=>'Super Admin','admin'=>'Admin','hr'=>'HR','pelamar'=>'Pelamar'];
  $roleName = $roleMap[$roleRaw] ?? Str::title($roleRaw);

  // ===== Email verified check (support default + fallback fields) =====
  $isVerified = false;
  if ($u) {
    $isVerified = method_exists($u, 'hasVerifiedEmail')
      ? $u->hasVerifiedEmail()
      : (bool) (($u->email_verified_at ?? null) ?: ($u->verified ?? false));
  }

  // ===== Avatar initials (defensive) =====
  $initials = '';
  if ($u && ($u->name ?? null)) {
    $parts = preg_split('/\s+/', trim((string) $u->name));
    $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
  }

  // ===== Base class helpers =====
  $activeBlue = fn($p) => request()->routeIs($p)
      ? 'border-blue-600 bg-blue-50 text-slate-900 font-semibold ring-1 ring-black/5'
      : 'border-transparent text-blue-800';
  $activeRed = fn($p) => request()->routeIs($p)
      ? 'border-red-600 bg-red-50 text-slate-900 font-semibold ring-1 ring-black/5'
      : 'border-transparent text-red-800';

  $baseLink = 'flex items-center gap-3 px-3 py-2 rounded-lg border-l-4 transition hover:bg-slate-50 hover:ring-1 hover:ring-black/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/20';
  $linkDeskBlue   = $baseLink;
  $linkDeskRed    = $baseLink;
  $linkMobileBlue = $baseLink;
  $linkMobileRed  = $baseLink;

  $iconBlue = 'inline-grid place-content-center w-7 h-7 rounded-md bg-blue-100 text-blue-700 ring-1 ring-black/5';
  $iconRed  = 'inline-grid place-content-center w-7 h-7 rounded-md bg-red-100  text-red-700  ring-1 ring-black/5';

  $sectionTitle = 'px-3 pt-4 pb-1 text-[11px] tracking-wide font-semibold uppercase text-slate-700';

  // ===== When not verified: visually dim links but keep clickable (go to verification) =====
  $lockVisual = !$isVerified ? 'opacity-70' : '';

  // ===== Route helper: if not verified, force to verification notice (GET) =====
  $verifyNoticeUrl = Route::has('verification.notice') ? route('verification.notice') : url('/email/verify');
  $href = function(string $routeName, array $params = []) use ($isVerified, $verifyNoticeUrl) {
    if (!$isVerified) return $verifyNoticeUrl;
    return Route::has($routeName) ? route($routeName, $params) : url('/');
  };
@endphp

{{-- ====== MINI MODE FIX (desktop sidebar compact) ====== --}}
<style>
@media (min-width: 768px){
  aside.is-mini a.account-card{justify-content:center;padding:.5rem .5rem!important}
  aside.is-mini a.account-card .account-info{display:none!important}
  aside.is-mini .login-hint{display:none!important}
  aside.is-mini form .btn{width:44px;height:44px;padding:0;border-radius:.75rem;display:flex;align-items:center;justify-content:center}
  aside.is-mini form .btn > span{gap:0}
  aside.is-mini form .btn .label{display:none}
}
</style>

@if($variant === 'desktop')
<nav class="p-3 space-y-1 text-sm text-slate-900 flex flex-col min-h-full">
  {{-- LOGO --}}
  <div class="mb-4">
    <a href="{{ url('/') }}"
       class="flex w-full items-center justify-center px-3 py-2 rounded-lg hover:bg-slate-50 hover:ring-1 hover:ring-black/5">
      <img
        src="{{ $logoUrl }}" alt="Logo"
        class="w-14 h-14 object-contain"
        loading="lazy" decoding="async" referrerpolicy="no-referrer"
        onerror="this.style.display='none'">
    </a>
  </div>

  {{-- ACCOUNT --}}
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-slate-900 align-middle mr-2"></span>
    <span class="align-middle text-slate-900">Account</span>
  </div>

  @auth
    <a href="{{ $href('profile.edit') }}" class="account-card mx-3 mb-2 block rounded-xl border border-slate-200 hover:bg-slate-50 hover:ring-1 hover:ring-black/5 transition {{ $lockVisual }}">
      <div class="flex items-center gap-3 px-3 py-2">
        @if(($u->profile_photo_url ?? null))
          <img src="{{ $u->profile_photo_url }}" alt="{{ e($u->name) }}" class="w-9 h-9 rounded-full object-cover ring-1 ring-black/5" loading="lazy" decoding="async">
        @else
          <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 grid place-content-center font-semibold ring-1 ring-black/5">
            {{ $u ? e($initials) : 'G' }}
          </div>
        @endif
        <div class="account-info min-w-0">
          <div class="text-xs text-slate-600 truncate max-w-[220px]">{{ e($u->email) }}</div>
          <div class="font-medium text-slate-900 truncate max-w-[180px]">{{ e($u->name) }}</div>
          <div class="mt-0.5 inline-flex items-center text:[10px] text-[10px] px-2 py-0.5 rounded-full {{ $isVerified ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }} ring-1 ring-black/5">
            {{ $isVerified ? 'Verified' : 'Belum Terverifikasi' }}
          </div>
        </div>
      </div>
    </a>

    {{-- Verification call-to-action (safe fallback if routes missing) --}}
    @if(!$isVerified)
      <div class="mx-3 mb-2 rounded-lg border border-red-200 bg-red-50 p-3">
        <div class="text-[12px] text-red-800 mb-2">Akun belum terverifikasi. Selesaikan verifikasi untuk akses menu.</div>
        @if (Route::has('verification.send'))
          <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="inline-flex items-center gap-2 rounded-md bg-red-600 text-white px-3 py-1.5 text-xs font-semibold hover:bg-red-700">
              {{-- send email again --}}
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12H8m0 0l3-3m-3 3 3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0Z"/>
              </svg>
              Kirim Ulang Email Verifikasi
            </button>
          </form>
        @endif
        <a href="{{ $verifyNoticeUrl }}" class="mt-2 inline-flex items-center gap-2 rounded-md border border-red-200 text-red-700 px-3 py-1.5 text-xs font-semibold hover:bg-red-100">
          Buka Halaman Verifikasi
        </a>
      </div>
    @endif
  @else
    <div class="login-hint px-3 text-xs text-slate-600 mb-1">Belum masuk (login)</div>
    <a href="{{ route('login') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('login') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15V18.75A2.25 2.25 0 0010.5 21h6.75A2.25 2.25 0 0019.5 18.75v-13.5A2 2 0 0017.25 3H10.5A2.25 2.25 0 008.25 5.25V9M15 12H3m0 0 3-3m-3 3 3 3"/>
        </svg>
      </span>
      <span class="label">Login</span>
    </a>
  @endauth

  {{-- GENERAL --}}
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-blue-700 align-middle mr-2"></span>
    <span class="align-middle text-blue-700">General</span>
  </div>

  <div class="{{ $lockVisual }}">
    <a href="{{ $href('jobs.index') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('jobs.*') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 116 0v1M6 11h12M5 17h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/>
        </svg>
      </span>
      <span class="label">Lowongan</span>
    </a>

    @auth
      <a href="{{ $href('dashboard') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('dashboard') }}">
        <span class="{{ $iconBlue }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h6.5v6.5h-6.5zM13.75 4.5h6.5v6.5h-6.5zM3.75 14.5h6.5v6.5h-6.5zM13.75 14.5h6.5v6.5h-6.5z"/>
          </svg>
        </span>
        <span class="label">Dashboard</span>
      </a>

      <a href="{{ $href('applications.mine') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('applications.mine') }}">
        <span class="{{ $iconBlue }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/>
          </svg>
        </span>
        <span class="label">Lamaran Saya</span>
      </a>
    @endauth
  </div>

  {{-- Divider --}}
  <div class="my-2 border-t border-slate-200"></div>

  {{-- ADMIN --}}
  @auth
  @if(in_array($roleRaw, ['hr','superadmin','admin'], true))
    <div class="{{ $sectionTitle }}">
      <span class="inline-block w-1.5 h-1.5 rounded-sm bg-red-700 align-middle mr-2"></span>
      <span class="align-middle text-red-700">Admin</span>
    </div>

    <div class="{{ $lockVisual }}">
      {{-- Companies (NEW) --}}
      @if (Route::has('admin.companies.index'))
        <a href="{{ $href('admin.companies.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.admin.companies.') }}">
          <span class="{{ $iconRed }}">
            {{-- office-building --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 8h.01M9 12h.01M9 16h.01M12 8h.01M12 12h.01M12 16h.01M15 8h.01M15 12h.01M15 16h.01"/>
            </svg>
          </span>
          <span class="label">Companies</span>
        </a>
      @endif

      @if (Route::has('admin.sites.index'))
        <a href="{{ $href('admin.sites.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.sites.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V5a2 2 0 012-2h5v18m7 0V9a2 2 0 00-2-2h-5"/>
            </svg>
          </span>
          <span class="label">Sites</span>
        </a>
      @endif

      <a href="{{ $href('admin.jobs.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.jobs.*') }}">
        <span class="{{ $iconRed }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 116 0v1M6 11h12M5 17h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/>
          </svg>
        </span>
        <span class="label">Jobs</span>
      </a>

      @if (Route::has('admin.candidates.index'))
        <a href="{{ $href('admin.candidates.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.candidates.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19a4 4 0 10-6 0M12 7a4 4 0 100-8 4 4 0 000 8m6 12v-1a4 4 0 00-4-4H10a4 4 0 00-4 4v1"/>
            </svg>
          </span>
          <span class="label">Candidates</span>
        </a>
      @endif

      <a href="{{ $href('admin.applications.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.applications.index') }}">
        <span class="{{ $iconRed }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/>
          </svg>
        </span>
        <span class="label">Applications</span>
      </a>

      <a href="{{ $href('admin.applications.board') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.applications.board') }}">
        <span class="{{ $iconRed }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z"/>
          </svg>
        </span>
        <span class="label">Kanban Board</span>
      </a>

      @if (Route::has('admin.interviews.index'))
        <a href="{{ $href('admin.interviews.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.interviews.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 018.75 6.5h6.5A6.25 6.25 0 0121.5 12.75v.25a4.75 4.75 0 01-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 014 13v-.25Z"/>
            </svg>
          </span>
          <span class="label">Interviews</span>
        </a>
      @endif

      @if (Route::has('admin.psychotests.index'))
        <a href="{{ $href('admin.psychotests.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.psychotests.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 2.5v4l-5.5 9A3 3 0 006 20.5h12a3 3 0 002.5-5l-5.5-9v-4"/>
            </svg>
          </span>
          <span class="label">Psychotests</span>
        </a>
      @endif

      @if (Route::has('admin.offers.index'))
        <a href="{{ $href('admin.offers.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.offers.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 004.5 4v16A2.5 2.5 0 007 22.5h10A2.5 2.5 0 0019.5 20V7.5Z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 13h7M8.5 16h7M8.5 10h4"/>
            </svg>
          </span>
          <span class="label">Offers</span>
        </a>
      @endif

      @if ($offerQuickId && Route::has('admin.offers.pdf'))
        <a href="{{ $href('admin.offers.pdf', $offerQuickId ? [$offerQuickId] : []) }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.offers.pdf') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 12 15.75l3-3m-3-10.5v10.5M6 19.5h12M19.5 7.5 13.5 1.5"/>
            </svg>
          </span>
          <span class="label">Offer PDF (quick)</span>
        </a>
      @endif

      @if (Route::has('admin.users.index'))
        <a href="{{ $href('admin.users.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.users.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 14a4 4 0 10-8 0v2a2 2 0 002 2h4a2 2 0 002-2v-2zM12 10a4 4 0 100-8 4 4 0 000 8z"/>
            </svg>
          </span>
          <span class="label">Users</span>
        </a>
      @endif

      @if ($roleRaw === 'superadmin' && Route::has('admin.audit_logs.index'))
        <a href="{{ $href('admin.audit_logs.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.audit_logs.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h11M9 12h11M9 17h11M5 7h.01M5 12h.01M5 17h.01"/>
            </svg>
          </span>
          <span class="label">Audit Logs</span>
        </a>
      @endif

      <a href="{{ $href('admin.dashboard.manpower') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.dashboard.manpower') }}">
        <span class="{{ $iconRed }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5h18M6 17V9m6 8V5m6 12v-6"/>
          </svg>
        </span>
        <span class="label">Manpower Dashboard</span>
      </a>
    </div>
  @endif
  @endauth

  {{-- Spacer --}}
  <div class="flex-1"></div>

  {{-- LOGOUT (sticky bottom) --}}
  @auth
    <form method="POST" action="{{ route('logout') }}" class="px-3 pb-2 pt-2">
      @csrf
      <button class="btn w-full rounded-lg border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 px-3 py-2 font-medium shadow-sm hover:shadow transition" title="Logout">
        <span class="inline-flex items-center gap-2 justify-center w-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H6.75A2.25 2.25 0 004.5 5.25v13.5A2.25 2.25 0 006.75 21H13.5a2.25 2.25 0 002.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3"/>
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
  {{-- LOGO --}}
  <a href="{{ url('/') }}" {!! $closeAttr !!} class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 hover:ring-1 hover:ring-black/5">
    <img src="{{ $logoUrl }}" alt="Logo" class="w-8 h-8 object-contain" loading="lazy" decoding="async" referrerpolicy="no-referrer"
         onerror="this.style.display='none'">
    <div class="leading-tight">
      <div class="font-semibold text-slate-900">{{ e($appName) }}</div>
      <div class="text-[10px] text-slate-500">Andalan Group</div>
    </div>
  </a>

  {{-- ACCOUNT --}}
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-slate-900 align-middle mr-2"></span>
    <span class="align-middle text-slate-900">Account</span>
  </div>

  @auth
    <a href="{{ $href('profile.edit') }}" {!! $closeAttr !!} class="account-card mx-3 mb-2 block rounded-xl border border-slate-200 hover:bg-slate-50 hover:ring-1 hover:ring-black/5 transition {{ $lockVisual }}">
      <div class="flex items-center gap-3 px-3 py-2">
        @if(($u->profile_photo_url ?? null))
          <img src="{{ $u->profile_photo_url }}" alt="{{ e($u->name) }}" class="w-9 h-9 rounded-full object-cover ring-1 ring-black/5" loading="lazy" decoding="async">
        @else
          <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 grid place-content-center font-semibold ring-1 ring-black/5">
            {{ $u ? e($initials) : 'G' }}
          </div>
        @endif
        <div class="account-info min-w-0">
          <div class="text-xs text-slate-600 truncate max-w-[240px]">{{ e($u->email) }}</div>
          <div class="font-medium text-slate-900 truncate max-w-[180px]">{{ e($u->name) }}</div>
          <div class="mt-0.5 inline-flex items-center text-[10px] px-2 py-0.5 rounded-full {{ $isVerified ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }} ring-1 ring-black/5">
            {{ $isVerified ? 'Verified' : 'Belum Terverifikasi' }}
          </div>
        </div>
      </div>
    </a>

    @if(!$isVerified)
      <div class="mx-3 mb-2 rounded-lg border border-red-200 bg-red-50 p-3">
        <div class="text-[12px] text-red-800 mb-2">Akun belum terverifikasi. Selesaikan verifikasi untuk akses menu.</div>
        @if (Route::has('verification.send'))
          <form method="POST" action="{{ route('verification.send') }}" {!! $closeAttr !!}>
            @csrf
            <button class="inline-flex items-center gap-2 rounded-md bg-red-600 text-white px-3 py-1.5 text-xs font-semibold hover:bg-red-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12H8m0 0l3-3m-3 3 3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0Z"/>
              </svg>
              Kirim Ulang Email Verifikasi
            </button>
          </form>
        @endif
        <a href="{{ $verifyNoticeUrl }}" {!! $closeAttr !!} class="mt-2 inline-flex items-center gap-2 rounded-md border border-red-200 text-red-700 px-3 py-1.5 text-xs font-semibold hover:bg-red-100">
          Buka Halaman Verifikasi
        </a>
      </div>
    @endif
  @else
    <div class="login-hint px-3 text-xs text-slate-600 mb-1">Belum masuk (login)</div>
    <a href="{{ route('login') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('login') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15V18.75A2.25 2.25 0 0010.5 21h6.75A2.25 2.25 0 0019.5 18.75v-13.5A2 2 0 0017.25 3H10.5A2.25 2.25 0 008.25 5.25V9M15 12H3m0 0 3-3m-3 3 3 3"/>
        </svg>
      </span>
      <span>Login</span>
    </a>
  @endauth

  {{-- GENERAL --}}
  <div class="{{ $sectionTitle }}">
    <span class="inline-block w-1.5 h-1.5 rounded-sm bg-blue-700 align-middle mr-2"></span>
    <span class="align-middle text-blue-700">General</span>
  </div>

  <div class="{{ $lockVisual }}">
    <a href="{{ $href('jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('jobs.*') }}">
      <span class="{{ $iconBlue }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 116 0v1M6 11h12M5 17h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/>
        </svg>
      </span>
      <span>Lowongan</span>
    </a>

    @auth
      <a href="{{ $href('dashboard') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('dashboard') }}">
        <span class="{{ $iconBlue }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h6.5v6.5h-6.5zM13.75 4.5h6.5v6.5h-6.5zM3.75 14.5h6.5v6.5h-6.5zM13.75 14.5h6.5v6.5h-6.5z"/>
          </svg>
        </span>
        <span>Dashboard</span>
      </a>

      <a href="{{ $href('applications.mine') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('applications.mine') }}">
        <span class="{{ $iconBlue }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/>
          </svg>
        </span>
        <span>Lamaran Saya</span>
      </a>
    @endauth
  </div>

  {{-- Divider --}}
  <div class="my-2 border-t border-slate-200"></div>

  {{-- ADMIN --}}
  @auth
  @if(in_array($roleRaw, ['hr','superadmin','admin'], true))
    <div class="{{ $sectionTitle }}">
      <span class="inline-block w-1.5 h-1.5 rounded-sm bg-red-700 align-middle mr-2"></span>
      <span class="align-middle text-red-700">Admin</span>
    </div>

    <div class="{{ $lockVisual }}">
      {{-- Companies (NEW) --}}
      @if (Route::has('admin.companies.index'))
        <a href="{{ $href('admin.companies.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.admin.companies.') }}">
          <span class="{{ $iconRed }}">
            {{-- office-building --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 8h.01M9 12h.01M9 16h.01M12 8h.01M12 12h.01M12 16h.01M15 8h.01M15 12h.01M15 16h.01"/>
            </svg>
          </span>
          <span>Companies</span>
        </a>
      @endif

      @if (Route::has('admin.sites.index'))
        <a href="{{ $href('admin.sites.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.sites.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V5a2 2 0 012-2h5v18m7 0V9a2 2 0 00-2-2h-5"/>
            </svg>
          </span>
          <span>Sites</span>
        </a>
      @endif

      <a href="{{ $href('admin.jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.jobs.*') }}">
        <span class="{{ $iconRed }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 116 0v1M6 11h12M5 17h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/>
          </svg>
        </span>
        <span>Jobs</span>
      </a>

      @if (Route::has('admin.candidates.index'))
        <a href="{{ $href('admin.candidates.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.candidates.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19a4 4 0 10-6 0M12 7a4 4 0 100-8 4 4 0 000 8m6 12v-1a4 4 0 00-4-4H10a4 4 0 00-4 4v1"/>
            </svg>
          </span>
          <span>Candidates</span>
        </a>
      @endif

      <a href="{{ $href('admin.applications.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.applications.index') }}">
        <span class="{{ $iconRed }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/>
          </svg>
        </span>
        <span>Applications</span>
      </a>

      <a href="{{ $href('admin.applications.board') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.applications.board') }}">
        <span class="{{ $iconRed }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z"/>
          </svg>
        </span>
        <span>Kanban Board</span>
      </a>

      @if (Route::has('admin.interviews.index'))
        <a href="{{ $href('admin.interviews.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.interviews.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 018.75 6.5h6.5A6.25 6.25 0 0121.5 12.75v.25a4.75 4.75 0 01-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 014 13v-.25Z"/>
            </svg>
          </span>
          <span>Interviews</span>
        </a>
      @endif

      @if (Route::has('admin.psychotests.index'))
        <a href="{{ $href('admin.psychotests.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.psychotests.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 2.5v4l-5.5 9A3 3 0 006 20.5h12a3 3 0 002.5-5l-5.5-9v-4"/>
            </svg>
          </span>
          <span>Psychotests</span>
        </a>
      @endif

      @if (Route::has('admin.offers.index'))
        <a href="{{ $href('admin.offers.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.offers.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 004.5 4v16A2.5 2.5 0 007 22.5h10A2.5 2.5 0 0019.5 20V7.5Z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 13h7M8.5 16h7M8.5 10h4"/>
            </svg>
          </span>
          <span>Offers</span>
        </a>
      @endif

      @if ($offerQuickId && Route::has('admin.offers.pdf'))
        <a href="{{ $href('admin.offers.pdf', $offerQuickId ? [$offerQuickId] : []) }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.offers.pdf') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 12 15.75l3-3m-3-10.5v10.5M6 19.5h12M19.5 7.5 13.5 1.5"/>
            </svg>
          </span>
          <span>Offer PDF (quick)</span>
        </a>
      @endif

      @if (Route::has('admin.users.index'))
        <a href="{{ $href('admin.users.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.users.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 14a4 4 0 10-8 0v2a2 2 0 002 2h4a2 2 0 002-2v-2zM12 10a4 4 0 100-8 4 4 0 000 8z"/>
            </svg>
          </span>
          <span>Users</span>
        </a>
      @endif

      @if ($roleRaw === 'superadmin' && Route::has('admin.audit_logs.index'))
        <a href="{{ $href('admin.audit_logs.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.audit_logs.*') }}">
          <span class="{{ $iconRed }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h11M9 12h11M9 17h11M5 7h.01M5 12h.01M5 17h.01"/>
            </svg>
          </span>
          <span>Audit Logs</span>
        </a>
      @endif

      <a href="{{ $href('admin.dashboard.manpower') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.dashboard.manpower') }}">
        <span class="{{ $iconRed }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5h18M6 17V9m6 8V5m6 12v-6"/>
          </svg>
        </span>
        <span>Manpower Dashboard</span>
      </a>
    </div>
  @endif
  @endauth

  {{-- Spacer --}}
  <div class="flex-1"></div>

  {{-- LOGOUT --}}
  @auth
    <form method="POST" action="{{ route('logout') }}" class="px-3 pb-2 pt-2">
      @csrf
      <button class="btn w-full rounded-lg border border-slate-200 bg-white text-slate-900 hover:bg-slate-50 px-3 py-2 font-medium shadow-sm transition" title="Logout">
        <span class="inline-flex items-center gap-2 justify-center w-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H6.75A2.25 2.25 0 004.5 5.25v13.5A2.25 2.25 0 006.75 21H13.5a2.25 2.25 0 002.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3"/>
          </svg>
          <span class="label">Logout</span>
        </span>
      </button>
    </form>
  @endauth
</nav>
@endif
