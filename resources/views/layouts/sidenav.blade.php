{{-- resources/views/layouts/partials/sidenav.blade.php --}}
@php
    use Illuminate\Support\Str;

    // ===== Props (opsional dari parent) =====
    $variant = $variant ?? 'desktop'; // 'desktop' | 'mobile'
    $closeOnClick = $closeOnClick ?? false;     // untuk mobile drawer
    $offerQuickId = $offerQuickId ?? null;
    $logoUrl = $logoUrl ?? asset('assets/logofix.png');
    $appName = config('app.name', 'Careers Portal');

    $closeAttr = $closeOnClick ? ' @click="open=false"' : '';

    // ===== Auth context =====
    /** @var \App\Models\User|null $u */
    $u = auth()->user();
    $roleRaw = $u->role ?? 'pelamar';
    $roleMap = ['superadmin' => 'Super Admin', 'admin' => 'Admin', 'hr' => 'HR', 'pelamar' => 'Pelamar'];
    $roleName = $roleMap[$roleRaw] ?? Str::title($roleRaw);

    // ===== Email verified (aman) =====
    $isVerified = false;
    if ($u) {
        $isVerified = method_exists($u, 'hasVerifiedEmail')
            ? $u->hasVerifiedEmail()
            : (bool) (($u->email_verified_at ?? null) ?: ($u->verified ?? false));
    }

    // ===== ADMIN ROLE CHECK (langsung, seperti sebelumnya) =====
    $hasAdminRole = false;
    if ($u) {
        $hasAdminRole = in_array(($u->role ?? ''), ['hr', 'admin', 'superadmin'], true);
        if (method_exists($u, 'hasAnyRole')) {
            $hasAdminRole = $hasAdminRole || $u->hasAnyRole(['hr', 'admin', 'superadmin']);
        }
    }

    // ===== Initials untuk avatar fallback =====
    $initials = '';
    if ($u && ($u->name ?? null)) {
        $parts = preg_split('/\s+/', trim((string) $u->name));
        $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    }

    // ===== Helper href: kalau belum verified -> arahkan ke notice =====
    $verifyNoticeUrl = Route::has('verification.notice') ? route('verification.notice') : url('/email/verify');
    $href = function (string $routeName, ...$params) use ($isVerified, $verifyNoticeUrl) {
        $params = $params[0] ?? [];
        if (!is_array($params)) {
            $params = [$params];
        }
        if (!$isVerified)
            return $verifyNoticeUrl;
        return Route::has($routeName) ? route($routeName, $params) : url('/');
    };

    // ===== Tema Warna (Putih & Brand #a77d52) =====
    $activeMenu = fn($p) => request()->routeIs($p)
        ? 'bg-[#a77d52]/15 text-[#a77d52] font-bold shadow-sm ring-1 ring-[#a77d52]/30 border-l-[#a77d52]'
        : 'text-slate-600 hover:bg-[#a77d52]/5 border-l-transparent';

    $baseLink = 'side-link flex items-center gap-3 px-3 py-2.5 rounded-xl border-l-4 border-l-transparent transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#a77d52]/30';
    $linkDesk = $baseLink . ' hover:bg-[#a77d52]/5';
    $linkMobile = $baseLink . ' hover:bg-[#a77d52]/5';

    /* ==== ICON WRAPPERS ==== */
    $iconWrap = 'grid place-items-center shrink-0 w-9 h-9 rounded-xl bg-[#a77d52]/10 text-[#a77d52] shadow-sm ring-1 ring-[#a77d52]/20 group-hover:scale-110 transition-transform';

    $sectionTitle = 'section-title px-3 pt-5 pb-1 text-[11px] tracking-widest font-bold uppercase text-[#a77d52]/60';
    $lockVisual = !$isVerified ? 'opacity-85' : '';

    // Container Menu
    $groupBox = 'group-box mx-0 mt-2 space-y-1.5 rounded-2xl border border-[#a77d52]/10 bg-[#a77d52]/[0.03] p-2 shadow-sm';

    // Kartu akun & tombol logout
    $accountCard = 'rounded-2xl border border-[#a77d52]/15 bg-[#a77d52]/5 hover:bg-[#a77d52]/10 transition-all duration-300 text-slate-700 shadow-sm';
    $logoutBtn = 'w-full rounded-2xl bg-[#a77d52] hover:bg-[#a77d52]/90 border border-[#a77d52] px-4 py-3 font-bold shadow-sm transition-all text-white active:scale-95';
@endphp

{{-- ====== MINI MODE & LOGO SIZING ====== --}}
<style>
  .sidenav-shell { width: 100%; }

  /* Desktop mini mode */
  @media (min-width: 768px) {
    aside.is-mini a.account-card { justify-content: center; padding: .5rem !important }
    aside.is-mini a.account-card .account-info, aside.is-mini .login-hint { display: none !important }
    aside.is-mini .section-title { text-align: center; padding-left: 0; padding-right: 0 }
    aside.is-mini .section-title span:not(:first-child) { display: none !important }
    aside.is-mini .group-box { margin-left: 0; margin-right: 0; padding: .4rem; border-radius: .9rem }
    aside.is-mini .side-link { justify-content: center; padding: .55rem !important; border-left-width: 0 !important }
    aside.is-mini .side-link .label { display: none !important }
    aside.is-mini form .btn { width: 44px; height: 44px; padding: 0; border-radius: .75rem; display: flex; align-items: center; justify-content: center }
    aside.is-mini form .btn>span { gap: 0 }
    aside.is-mini form .btn .label { display: none }
    aside.is-mini .logo-wrap { min-height: 56px; padding: .25rem .5rem !important }
    aside.is-mini .logo-img { max-height: 40px !important; max-width: 100% !important }
  }

  /* Logo wrapper agar responsif dan tidak overflow */
  .logo-wrap { min-height: 80px; width: 100% }
  .logo-img  { max-height: 64px; max-width: 100%; width: auto; object-fit: contain; }
</style>

@if($variant === 'desktop')
    <nav class="flex flex-col min-h-full p-3 space-y-1 text-sm text-slate-700 sidenav-shell">
      {{-- LOGO --}}
      <div class="mb-3">
        <a href="{{ url('/') }}" class="flex items-center justify-center w-full px-0 py-3 rounded-2xl logo-wrap bg-white shadow-sm ring-1 ring-black/5 hover:brightness-95 transition-all">
          <img src="{{ $logoUrl }}" alt="Logo Andalan" class="logo-img" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.style.display='none'">
        </a>
      </div>

      {{-- ACCOUNT --}}
      <div class="{{ $sectionTitle }} text-center">
        <span class="inline-block w-1.5 h-1.5 rounded-sm bg-[#a77d52] align-middle mr-2"></span>
        <span class="align-middle">Account</span>
      </div>

      @auth
          <a href="{{ $href('profile.edit') }}" class="account-card mx-0 mb-2 block {{ $accountCard }} {{ $lockVisual }}">
            <div class="flex items-center gap-3 px-3 py-2">
              @if(($u->profile_photo_url ?? null))
                {{-- AVATAR diperkecil --}}
                <img src="{{ $u->profile_photo_url }}" alt="{{ e($u->name) }}" class="object-cover w-8 h-8 rounded-full ring-1 ring-white/50" loading="lazy" decoding="async">
              @else
                <div class="grid w-8 h-8 font-semibold text-[#800000] bg-white rounded-full place-content-center ring-2 ring-white/20">
                  {{ $u ? e($initials) : 'G' }}
                </div>
              @endif
              <div class="min-w-0 account-info">
                <div class="text-xs text-black truncate max-w-[220px]">{{ e($u->email) }}</div>
                <div class="font-medium text-black truncate max-w-[180px]">{{ e($u->name) }}</div>
                <div class="mt-0.5 inline-flex items-center text-[10px] px-2 py-0.5 rounded-full {{ $isVerified ? 'bg-white/20 text-black ring-1 ring-white/35' : 'bg-white/15 text-black ring-1 ring-white/30' }}">
                  {{ $isVerified ? 'Verified' : 'Belum Terverifikasi' }}
                </div>
              </div>
            </div>
          </a>

          @if(!$isVerified)
            <div class="p-3 mx-0 mb-2 border border-red-200 rounded-lg bg-red-50">
              <div class="text-[12px] text-red-800 mb-2">Akun belum terverifikasi. Selesaikan verifikasi untuk akses menu.</div>
              @if (Route::has('verification.send'))
                  <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-md bg-red-600 text-white px-3 py-1.5 text-xs font-semibold hover:bg-red-700">
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
        <div class="{{ $groupBox }} {{ $lockVisual }}">
          <a href="{{ route('login') }}" class="{{ $linkDesk }} {{ $activeMenu('login') }}">
            <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15V18.75A2.25 2.25 0 0010.5 21h6.75A2.25 2.25 0 0019.5 18.75v-13.5A2 2 0 0017.25 3H10.5A2.25 2.25 0 008.25 5.25V9M15 12H3m0 0 3-3m-3 3 3 3"/></svg></span>
            <span class="label">Login</span>
          </a>
        </div>
      @endauth

      {{-- GENERAL --}}
      <div class="{{ $sectionTitle }} text-center">
        <span class="inline-block w-1.5 h-1.5 rounded-sm bg-[#a77d52] align-middle mr-2"></span>
        <span class="align-middle">General</span>
      </div>

      <div class="{{ $groupBox }} {{ $lockVisual }}">
        <a href="{{ $href('jobs.index') }}" class="{{ $linkDesk }} {{ $activeMenu('jobs.*') }}">
          <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 116 0v1M6 11h12M5 17h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></span>
          <span class="label">Lowongan</span>
        </a>

        @auth
            <a href="{{ $href('applications.mine') }}" class="{{ $linkDesk }} {{ $activeMenu('applications.mine') }}">
              <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6M9 3H8a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-1"/></svg></span>
              <span class="label">Lamaran Saya</span>
            </a>

            @if(in_array($u->role ?? '', ['pelamar', 'trainer', 'karyawan']))
              <a href="{{ route('kanban.mine') }}" class="{{ $linkDesk }} {{ $activeMenu('kanban.mine') }}">
                <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z"/></svg></span>
                <span class="label">Kanban Board</span>
              </a>
              <a href="{{ $href('me.interviews.index') }}" class="{{ $linkDesk }} {{ $activeMenu('me.interviews.*') }}">
                <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 018.75 6.5h6.5A6.25 6.25 0 0121.5 12.75v.25a4.75 4.75 0 01-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 014 13v-.25Z"/></svg></span>
                <span class="label">Schedule Interview</span>
              </a>
            @endif
        @endauth
      </div>

      {{-- ADMIN --}}
      @auth
          @if($hasAdminRole)
              <div class="{{ $sectionTitle }} text-center">
                <span class="inline-block w-1.5 h-1.5 rounded-sm bg-[#a77d52] align-middle mr-2"></span>
                <span class="align-middle">Admin</span>
              </div>

              <div class="{{ $groupBox }} {{ $lockVisual }}">
                @if (Route::has('admin.companies.index'))
                    <a href="{{ $href('admin.companies.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.companies.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 8h.01M9 12h.01M9 16h.01M12 8h.01M12 12h.01M12 16h.01M15 8h.01M15 12h.01M15 16h.01"/></svg></span>
                      <span class="label">Companies</span>
                    </a>
                @endif
                @if (Route::has('admin.pohs.index'))
                    <a href="{{ $href('admin.pohs.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.pohs.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg></span>
                      <span class="label">POH</span>
                    </a>
                @endif

                @if (Route::has('admin.sites.index'))
                    <a href="{{ $href('admin.sites.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.sites.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V5a2 2 0 012-2h5v18m7 0V9a2 2 0 00-2-2h-5"/></svg></span>
                      <span class="label">Sites</span>
                    </a>
                @endif

                <a href="{{ $href('admin.jobs.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.jobs.*') }}">
                  <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 116 0v1M6 11h12M5 17h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></span>
                  <span class="label">Jobs</span>
                </a>

                @if (Route::has('admin.candidates.index'))
                    <a href="{{ $href('admin.candidates.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.candidates.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19a4 4 0 10-6 0M12 7a4 4 0 100-8 4 4 0 000 8m6 12v-1a4 4 0 00-4-4H10a4 4 0 00-4 4v1"/></svg></span>
                      <span class="label">Candidates</span>
                    </a>
                @endif

                <a href="{{ $href('admin.applications.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.applications.index') }}">
                  <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/></svg></span>
                  <span class="label">Applications</span>
                </a>

                <a href="{{ $href('admin.applications.board') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.applications.board') }}">
                  <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z"/></svg></span>
                  <span class="label">Kanban Board</span>
                </a>

                @if (Route::has('admin.interviews.index'))
                    <a href="{{ $href('admin.interviews.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.interviews.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 018.75 6.5h6.5A6.25 6.25 0 0121.5 12.75v.25a4.75 4.75 0 01-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 014 13v-.25Z"/></svg></span>
                      <span class="label">Interviews</span>
                    </a>
                @endif

                @if (Route::has('admin.psychotests.index'))
                    <a href="{{ $href('admin.psychotests.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.psychotests.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 2.5v4l-5.5 9A3 3 0 006 20.5h12a3 3 0 002.5-5l-5.5-9v-4"/></svg></span>
                      <span class="label">Psychotests</span>
                    </a>
                @endif

                @if (Route::has('admin.offers.index'))
                    <a href="{{ $href('admin.offers.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.offers.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 004.5 4v16A2.5 2.5 0 007 22.5h10A2.5 2.5 0 0019.5 20V7.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 13h7M8.5 16h7M8.5 10h4"/></svg></span>
                      <span class="label">Offers</span>
                    </a>
                @endif

                @if (Route::has('admin.mcu-templates.index'))
                    <a href="{{ $href('admin.mcu-templates.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.mcu-templates.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/></svg></span>
                      <span class="label">MCU Templates</span>
                    </a>
                @endif

                @if ($offerQuickId && Route::has('admin.offers.pdf'))
                    <a href="{{ $href('admin.offers.pdf', $offerQuickId ? [$offerQuickId] : []) }}" class="{{ $linkDesk }} {{ $activeMenu('admin.offers.pdf') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 12 15.75l3-3m-3-10.5v10.5M6 19.5h12M19.5 7.5 13.5 1.5"/></svg></span>
                      <span class="label">Offer PDF (quick)</span>
                    </a>
                @endif

                @if (Route::has('admin.users.index'))
                    <a href="{{ $href('admin.users.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.users.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 14a4 4 0 10-8 0v2a2 2 0 002 2h4a2 2 0 002-2v-2zM12 10a4 4 0 100-8 4 4 0 000 8z"/></svg></span>
                      <span class="label">Users</span>
                    </a>
                @endif

                @if ($roleRaw === 'superadmin' && Route::has('admin.audit_logs.index'))
                    <a href="{{ $href('admin.audit_logs.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.audit_logs.*') }}">
                      <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h11M9 12h11M9 17h11M5 7h.01M5 12h.01M5 17h.01"/></svg></span>
                      <span class="label">Audit Logs</span>
                    </a>
                @endif

                <a href="{{ $href('admin.dashboard.manpower') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.dashboard.manpower') }}">
                  <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5h18M6 17V9m6 8V5m6 12v-6"/></svg></span>
                  <span class="label">Manpower Dashboard</span>
                </a>
              </div>
          @endif
      @endauth

      <div class="flex-1"></div>

      {{-- LOGOUT --}}
      @auth
          <form method="POST" action="{{ route('logout') }}" class="px-0 pt-2 pb-2">
            @csrf
            <button class="btn {{ $logoutBtn }}" title="Logout">
              <span class="inline-flex items-center justify-center w-full gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white/90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H6.75A2.25 2.25 0 004.5 5.25v13.5A2.25 2.25 0 006.75 21H13.5a2.25 2.25 0 002.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3"/>
                </svg>
                <span class="label">Logout</span>
              </span>
            </button>
          </form>
      @endauth
    </nav>

@else
    <nav class="flex flex-col min-h-full space-y-1 text-sm text-slate-700 sidenav-shell">
      <div class="px-2 py-3">
        <a href="{{ url('/') }}" {!! $closeAttr !!} class="flex items-center gap-3 px-3 py-2 rounded-2xl bg-white shadow-sm ring-1 ring-black/5 hover:bg-[#a77d52]/10 transition-colors">
          <img src="{{ $logoUrl }}" alt="Logo" class="object-contain w-7 h-7" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.style.display='none'">
          <div class="leading-tight">
            <div class="font-bold text-slate-900">{{ e($appName) }}</div>
            <div class="text-[10px] text-slate-500">Andalan Group</div>
          </div>
        </a>
      </div>

      <div class="{{ $sectionTitle }} text-center">
        <span class="inline-block w-1.5 h-1.5 rounded-sm bg-[#a77d52] align-middle mr-2"></span>
        <span class="align-middle">Account</span>
      </div>

      @auth
          <a href="{{ $href('profile.edit') }}" {!! $closeAttr !!} class="account-card mx-0 mb-2 block {{ $accountCard }} {{ $lockVisual }}">
            <div class="flex items-center gap-3 px-3 py-2">
              @if(($u->profile_photo_url ?? null))
                <img src="{{ $u->profile_photo_url }}" alt="{{ e($u->name) }}" class="object-cover w-8 h-8 rounded-full ring-1 ring-white/50" loading="lazy" decoding="async">
              @else
                <div class="grid w-8 h-8 font-semibold text-[#5d0e11] bg-white rounded-full place-content-center ring-2 ring-white/20">{{ $u ? e($initials) : 'G' }}</div>
              @endif
              <div class="min-w-0 account-info">
                <div class="text-xs text-white/85 truncate max-w-[240px]">{{ e($u->email) }}</div>
                <div class="font-medium text-white truncate max-w-[180px]">{{ e($u->name) }}</div>
                <div class="mt-0.5 inline-flex items-center text-[10px] px-2 py-0.5 rounded-full {{ $isVerified ? 'bg-white/20 text-white ring-1 ring-white/35' : 'bg-white/15 text-white ring-1 ring-white/30' }}">
                  {{ $isVerified ? 'Verified' : 'Belum Terverifikasi' }}
                </div>
              </div>
            </div>
          </a>

          @if(!$isVerified)
              <div class="p-3 mx-0 mb-2 border border-red-200 rounded-lg bg-red-50">
                <div class="text-[12px] text-red-800 mb-2">Akun belum terverifikasi. Selesaikan verifikasi untuk akses menu.</div>
                @if (Route::has('verification.send'))
                    <form method="POST" action="{{ route('verification.send') }}" {!! $closeAttr !!}>
                      @csrf
                      <button class="inline-flex items-center gap-2 rounded-md bg-red-600 text-white px-3 py-1.5 text-xs font-semibold hover:bg-red-700">
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
        <div class="{{ $groupBox }} {{ $lockVisual }}">
          <a href="{{ route('login') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('login') }}">
            <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15V18.75A2.25 2.25 0 0010.5 21h6.75A2.25 2.25 0 0019.5 18.75v-13.5A2 2 0 0017.25 3H10.5A2.25 2.25 0 008.25 5.25V9M15 12H3m0 0 3-3m-3 3 3 3"/></svg></span>
            <span>Login</span>
          </a>
        </div>
      @endauth

      <div class="{{ $sectionTitle }} text-center">
        <span class="inline-block w-1.5 h-1.5 rounded-sm bg-[#a77d52] align-middle mr-2"></span>
        <span class="align-middle">General</span>
      </div>

      <div class="{{ $groupBox }} {{ $lockVisual }}">
        <a href="{{ $href('jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('jobs.*') }}">
      <div class="{{ $groupBox }} {{ $lockVisual }}">
        <a href="{{ $href('jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('jobs.*') }}">
          <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 116 0v1M6 11h12M5 17h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></span>
          <span>Lowongan</span>
        </a>

        @auth
            <a href="{{ $href('dashboard') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('dashboard') }}">
              <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h6.5v6.5h-6.5zM13.75 4.5h6.5v6.5h-6.5zM3.75 14.5h6.5v6.5h-6.5zM13.75 14.5h6.5v6.5h-6.5z"/></svg></span>
              <span>Dashboard</span>
            </a>

            <a href="{{ $href('applications.mine') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('applications.mine') }}">
              <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/></svg></span>
              <span>Lamaran Saya</span>
            </a>

            @if(in_array($u->role ?? '', ['pelamar', 'trainer', 'karyawan']))
              <a href="{{ route('kanban.mine') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('kanban.mine') }}">
                <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z"/></svg></span>
                <span>Kanban Board</span>
              </a>
              <a href="{{ $href('me.interviews.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('me.interviews.*') }}">
                <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 018.75 6.5h6.5A6.25 6.25 0 0121.5 12.75v.25a4.75 4.75 0 01-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 014 13v-.25Z"/></svg></span>
                <span>Schedule Interview</span>
              </a>
            @endif
        @endauth
      </div>

      {{-- ADMIN --}}
      @auth
          @if($hasAdminRole)
              <div class="{{ $sectionTitle }} text-center">
                <span class="inline-block w-1.5 h-1.5 rounded-sm bg-[#a77d52] align-middle mr-2"></span>
                <span class="align-middle">Admin</span>
              </div>

              <div class="{{ $groupBox }} {{ $lockVisual }}">
                @if (Route::has('admin.companies.index'))
                      <a href="{{ $href('admin.companies.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.companies.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 8h.01M9 12h.01M9 16h.01M12 8h.01M12 12h.01M12 16h.01M15 8h.01M15 12h.01M15 16h.01"/></svg></span>
                        <span>Companies</span>
                      </a>
                @endif
                @if (Route::has('admin.pohs.index'))
                      <a href="{{ $href('admin.pohs.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.pohs.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg></span>
                        <span>POH</span>
                      </a>
                @endif

                @if (Route::has('admin.sites.index'))
                      <a href="{{ $href('admin.sites.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.sites.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V5a2 2 0 012-2h5v18m7 0V9a2 2 0 00-2-2h-5"/></svg></span>
                        <span>Sites</span>
                      </a>
                @endif

                <a href="{{ $href('admin.jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.jobs.*') }}">
                  <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 116 0v1M6 11h12M5 17h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></span>
                  <span>Jobs</span>
                </a>

                @if (Route::has('admin.candidates.index'))
                      <a href="{{ $href('admin.candidates.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.candidates.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19a4 4 0 10-6 0M12 7a4 4 0 100-8 4 4 0 000 8m6 12v-1a4 4 0 00-4-4H10a4 4 0 00-4 4v1"/></svg></span>
                        <span>Candidates</span>
                      </a>
                @endif

                <a href="{{ $href('admin.applications.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.applications.index') }}">
                  <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/></svg></span>
                  <span>Applications</span>
                </a>

                <a href="{{ $href('admin.applications.board') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.applications.board') }}">
                  <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z"/></svg></span>
                  <span>Kanban Board</span>
                </a>

                @if (Route::has('admin.interviews.index'))
                      <a href="{{ $href('admin.interviews.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.interviews.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 018.75 6.5h6.5A6.25 6.25 0 0121.5 12.75v.25a4.75 4.75 0 01-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 014 13v-.25Z"/></svg></span>
                        <span>Interviews</span>
                      </a>
                @endif

                @if (Route::has('admin.psychotests.index'))
                      <a href="{{ $href('admin.psychotests.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.psychotests.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 2.5v4l-5.5 9A3 3 0 006 20.5h12a3 3 0 002.5-5l-5.5-9v-4"/></svg></span>
                        <span>Psychotests</span>
                      </a>
                @endif

                @if (Route::has('admin.offers.index'))
                      <a href="{{ $href('admin.offers.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.offers.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 004.5 4v16A2.5 2.5 0 007 22.5h10A2.5 2.5 0 0019.5 20V7.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 13h7M8.5 16h7M8.5 10h4"/></svg></span>
                        <span>Offers</span>
                      </a>
                @endif

                @if (Route::has('admin.mcu-templates.index'))
                      <a href="{{ $href('admin.mcu-templates.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.mcu-templates.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/></svg></span>
                        <span>MCU Templates</span>
                      </a>
                @endif

                @if ($offerQuickId && Route::has('admin.offers.pdf'))
                      <a href="{{ $href('admin.offers.pdf', $offerQuickId ? [$offerQuickId] : []) }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.offers.pdf') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 12 15.75l3-3m-3-10.5v10.5M6 19.5h12M19.5 7.5 13.5 1.5"/></svg></span>
                        <span>Offer PDF (quick)</span>
                      </a>
                @endif

                @if (Route::has('admin.users.index'))
                      <a href="{{ $href('admin.users.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.users.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 14a4 4 0 10-8 0v2a2 2 0 002 2h4a2 2 0 002-2v-2zM12 10a4 4 0 100-8 4 4 0 000 8z"/></svg></span>
                        <span>Users</span>
                      </a>
                @endif

                @if ($roleRaw === 'superadmin' && Route::has('admin.audit_logs.index'))
                      <a href="{{ $href('admin.audit_logs.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.audit_logs.*') }}">
                        <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h11M9 12h11M9 17h11M5 7h.01M5 12h.01M5 17h.01"/></svg></span>
                        <span>Audit Logs</span>
                      </a>
                @endif

                <a href="{{ $href('admin.dashboard.manpower') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.dashboard.manpower') }}">
                  <span class="{{ $iconWrap }}"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5h18M6 17V9m6 8V5m6 12v-6"/></svg></span>
                  <span>Manpower Dashboard</span>
                </a>
              </div>
          @endif
      @endauth

      <div class="flex-1"></div>

      @auth
          <form method="POST" action="{{ route('logout') }}" class="px-0 pt-2 pb-2">
            @csrf
            <button class="btn {{ $logoutBtn }}" title="Logout" {!! $closeAttr !!}>
              <span class="inline-flex items-center justify-center w-full gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white/90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H6.75A2.25 2.25 0 004.5 5.25v13.5A2.25 2.25 0 006.75 21H13.5a2.25 2.25 0 002.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3"/></svg>
                <span class="label">Logout</span>
              </span>
            </button>
          </form>
      @endauth
    </nav>
@endif
