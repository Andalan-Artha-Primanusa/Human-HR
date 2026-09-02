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

    // ===== Tema Warna: sidebar netral, brown hanya untuk accent =====
    $activeMenu = fn($p) => request()->routeIs($p)
        ? 'is-active text-white font-bold border-l-[#d7b98e]'
        : 'text-slate-200 border-l-transparent';

    $baseLink = 'side-link flex items-center gap-3 px-3 py-2.5 rounded-xl border-l-4 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30';
    $linkDesk = $baseLink;
    $linkMobile = $baseLink;

    /* ==== ICON WRAPPERS ==== */
    $iconWrap = 'side-icon grid place-items-center shrink-0 w-8 h-8 rounded-md text-slate-300 transition md:w-8 md:h-8';
    $menuIcon = function (string $name): string {
        $attrs = 'xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"';
        $paths = [
            'login' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/>',
            'jobs' => '<rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M8 7V5.5A2.5 2.5 0 0 1 10.5 3h3A2.5 2.5 0 0 1 16 5.5V7"/><path d="M3 13h18"/>',
            'applications' => '<path d="M8 4h8l2 2v14H6V6l2-2Z"/><path d="M9 12h6"/><path d="M9 16h6"/><path d="M9 8h4"/>',
            'interviews' => '<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 9h18"/><path d="M8 14h4"/><path d="M8 17h7"/>',
            'kanban' => '<rect x="3" y="4" width="5" height="16" rx="1.5"/><rect x="10" y="4" width="5" height="10" rx="1.5"/><rect x="17" y="4" width="4" height="13" rx="1.5"/>',
            'dashboard' => '<rect x="3" y="3" width="7" height="8" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="15" width="7" height="6" rx="1.5"/>',
            'candidates' => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.65"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'audit' => '<path d="M9 11h6"/><path d="M9 15h6"/><path d="M9 7h3"/><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 3v3h8V3"/>',
        ];
        return '<svg ' . $attrs . '>' . ($paths[$name] ?? $paths['jobs']) . '</svg>';
    };

    $sectionTitle = 'section-title px-3 pt-5 pb-1 text-[11px] tracking-widest font-bold uppercase text-slate-400';
    $lockVisual = !$isVerified ? 'opacity-85' : '';

    // Container Menu
    $groupBox = 'group-box mx-0 mt-1 space-y-0.5 rounded-xl p-1 text-slate-200';

    // Kartu akun
    $accountCard = 'rounded-2xl border border-white/10 bg-white/[0.06] hover:bg-white/[0.1] transition-all duration-300 text-white shadow-sm';
@endphp

{{-- ====== SIDEBAR STYLES ====== --}}
<style>
  /* ===== SHELL ===== */
  .sidenav-shell { width:100%; background:#263241; border-radius:1rem; overflow:hidden; }
  nav.sidenav-shell { color:#fff !important; }

  /* ===== LOGO ===== */
  .logo-wrap { min-height:72px; width:100%; background:rgba(255,255,255,0.06); border-radius:.875rem; display:flex; align-items:center; justify-content:center; padding:.5rem !important; border:1px solid rgba(255,255,255,0.1); transition:background .2s; }
  .logo-wrap:hover { background:rgba(255,255,255,0.1); }
  .logo-img { max-height:52px; max-width:100%; width:auto; object-fit:contain; filter:brightness(0) invert(1); }

  /* ===== SECTION TITLES ===== */
  .section-title { color:rgba(226,232,240,0.58) !important; font-size:10px; letter-spacing:.1em; font-weight:700; text-transform:uppercase; padding:1.25rem .75rem .4rem; }
  .section-title span.inline-block { background:#d7b98e !important; }

  /* ===== GROUP BOX ===== */
  .group-box { background:transparent !important; border:0 !important; border-radius:.75rem; padding:.15rem !important; margin-top:.1rem !important; }

  /* ===== MENU LINKS — netral, active saja yang brand ===== */
  .side-link { color:#d7dee8 !important; border-left-color:transparent !important; border-radius:.55rem !important; padding:.56rem .72rem !important; transition:background .18s, color .18s, border-color .18s !important; font-weight:650; }
  .side-link:hover { background:rgba(255,255,255,0.055) !important; color:#fff !important; }

  /* ===== ACTIVE — maroon gelap (#5c3d1e) ===== */
  .side-link.is-active,
  .side-link.font-bold {
    background:rgba(167,125,82,0.2) !important;
    color:#fff !important;
    font-weight:700 !important;
    border-left-color:#d7b98e !important;
    box-shadow:inset 0 0 0 1px rgba(215,185,142,0.08) !important;
  }

  /* ===== ICON WRAP ===== */
  .side-link .side-icon { background:transparent !important; color:#aeb9c8 !important; border:0 !important; box-shadow:none !important; }
  .side-link .side-icon svg { width:1.18rem !important; height:1.18rem !important; stroke-width:2.25 !important; }
  .side-link:hover .side-icon { background:rgba(255,255,255,0.06) !important; color:#fff !important; }
  .side-link.is-active .side-icon,
  .side-link.font-bold .side-icon { background:rgba(215,185,142,0.16) !important; color:#f4d9b2 !important; }

  /* ===== ACCOUNT CARD ===== */
  a.account-card { background:rgba(255,255,255,0.06) !important; border:1px solid rgba(255,255,255,0.1) !important; border-radius:.875rem !important; color:#fff !important; transition:background .2s !important; }
  a.account-card:hover { background:rgba(255,255,255,0.1) !important; }
  a.account-card .account-info div, a.account-card .account-info span { color:#fff !important; }
  a.account-card .account-info .text-xs { color:rgba(255,255,255,0.65) !important; }
  a.account-card div.rounded-full { background:#d7b98e !important; color:#263241 !important; }
  a.account-card .inline-flex.rounded-full { background:rgba(215,185,142,0.16) !important; color:#f8ead6 !important; }

  /* ===== LOGOUT ===== */
  form .btn { background:transparent !important; border:1px solid rgba(248,113,113,0.28) !important; color:#fee2e2 !important; border-radius:.875rem !important; transition:background .2s, color .2s !important; box-shadow:none !important; }
  form .btn:hover { background:rgba(239,68,68,0.12) !important; color:#fff !important; }

  /* ===== MINI MODE ===== */
  @media (min-width:768px) {
    aside.is-mini a.account-card { justify-content:center; padding:.5rem !important }
    aside.is-mini a.account-card .account-info, aside.is-mini .login-hint { display:none !important }
    aside.is-mini .section-title { text-align:center; padding-left:0; padding-right:0 }
    aside.is-mini .section-title span:not(:first-child) { display:none !important }
    aside.is-mini .group-box { padding:.35rem !important; border-radius:.75rem }
    aside.is-mini .side-link { justify-content:center; padding:.55rem !important; border-left-width:0 !important }
    aside.is-mini .side-link .label { display:none !important }
    aside.is-mini form .btn { width:44px; height:44px; padding:0; border-radius:.75rem; display:flex; align-items:center; justify-content:center }
    aside.is-mini form .btn>span { gap:0 }
    aside.is-mini form .btn .label { display:none }
    aside.is-mini .logo-wrap { min-height:56px; padding:.25rem .5rem !important }
    aside.is-mini .logo-img { max-height:38px !important; }
  }
  /* ===== MOBILE ===== */
  @media (max-width:767px) {
    .sidenav-shell { padding:0.5rem !important; border-radius:.75rem; }
    .logo-wrap { min-height:56px; }
    .logo-img { max-height:44px !important; }
    .group-box { padding:0.35rem !important; }
    .side-link { padding:0.6rem 0.5rem !important; }
    .side-link .label { font-size:0.875rem !important; }
  }
</style>

@if($variant === 'desktop')
    <nav class="flex flex-col min-h-full p-3 space-y-1 text-sm text-white sidenav-shell">
      {{-- LOGO --}}
      <div class="mb-3">
        <a href="{{ url('/') }}" class="flex items-center justify-center w-full px-0 py-3 rounded-2xl logo-wrap bg-white shadow-sm ring-1 ring-black/5 hover:brightness-95 transition-all">
          <img src="{{ $logoUrl }}" alt="Logo Andalan" class="logo-img" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.style.display='none'">
        </a>
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
                <div class="text-xs text-white truncate max-w-[220px]">{{ e($u->email) }}</div>
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
            <span class="{{ $iconWrap }}">{!! $menuIcon('login') !!}</span>
            <span class="label text-white">Login</span>
          </a>
        </div>
      @endauth

      <div class="{{ $groupBox }} {{ $lockVisual }}">
        <a href="{{ $href('jobs.index') }}" class="{{ $linkDesk }} {{ $activeMenu('jobs.*') }}">
          <span class="{{ $iconWrap }}">{!! $menuIcon('jobs') !!}</span>
          <span class="label text-white">Lowongan</span>
        </a>

@auth
            <a href="{{ $href('applications.mine') }}" class="{{ $linkDesk }} {{ $activeMenu('applications.mine') }}">
              <span class="{{ $iconWrap }}">{!! $menuIcon('applications') !!}</span>
              <span class="label text-white">Lamaran Saya</span>
            </a>

            <a href="{{ $href('me.interviews.index') }}" class="{{ $linkDesk }} {{ $activeMenu('me.interviews.*') }}">
              <span class="{{ $iconWrap }}">{!! $menuIcon('interviews') !!}</span>
              <span class="label text-white">Interview</span>
            </a>

            @if(in_array($u->role ?? '', ['trainer', 'karyawan']))
              <a href="{{ route('kanban.mine') }}" class="{{ $linkDesk }} {{ $activeMenu('kanban.mine') }}">
                <span class="{{ $iconWrap }}">{!! $menuIcon('kanban') !!}</span>
                <span class="label text-white">Kanban Board</span>
              </a>
            @endif
        @endauth
      </div>

      {{-- ADMIN --}}
      @auth
          @if($hasAdminRole)
              <div class="{{ $groupBox }} {{ $lockVisual }}">
                <a href="{{ $href('dashboard') }}" class="{{ $linkDesk }} {{ $activeMenu('dashboard') }}">
                  <span class="{{ $iconWrap }}">{!! $menuIcon('dashboard') !!}</span>
                  <span class="label text-white">Dashboard</span>
                </a>

                <a href="{{ $href('admin.jobs.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.jobs.*') }}">
                  <span class="{{ $iconWrap }}">{!! $menuIcon('jobs') !!}</span>
                  <span class="label text-white">Jobs</span>
                </a>

                @if (Route::has('admin.candidates.index'))
                    <a href="{{ $href('admin.candidates.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.candidates.*') }}">
                      <span class="{{ $iconWrap }}">{!! $menuIcon('candidates') !!}</span>
                      <span class="label text-white">Candidates</span>
                    </a>
                @endif

                <a href="{{ $href('admin.applications.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.applications.index') }}">
                  <span class="{{ $iconWrap }}">{!! $menuIcon('applications') !!}</span>
                  <span class="label text-white">Applications</span>
                </a>

                <a href="{{ $href('admin.applications.board') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.applications.board') }}">
                  <span class="{{ $iconWrap }}">{!! $menuIcon('kanban') !!}</span>
                  <span class="label text-white">Kanban Board</span>
                </a>

                @if (Route::has('admin.interviews.index'))
                    <a href="{{ $href('admin.interviews.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.interviews.*') }}">
                      <span class="{{ $iconWrap }}">{!! $menuIcon('interviews') !!}</span>
                      <span class="label text-white">Interviews</span>
                    </a>
                @endif

                @if (Route::has('admin.users.index'))
                    <a href="{{ $href('admin.users.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.users.*') }}">
                      <span class="{{ $iconWrap }}">{!! $menuIcon('users') !!}</span>
                      <span class="label text-white">Users</span>
                    </a>
                @endif

                @if ($roleRaw === 'superadmin' && Route::has('admin.audit_logs.index'))
                    <a href="{{ $href('admin.audit_logs.index') }}" class="{{ $linkDesk }} {{ $activeMenu('admin.audit_logs.*') }}">
                      <span class="{{ $iconWrap }}">{!! $menuIcon('audit') !!}</span>
                      <span class="label text-white">Audit Logs</span>
                    </a>
                @endif

              </div>
          @endif
      @endauth

      <div class="flex-1"></div>

    </nav>

@else
    <nav class="flex flex-col min-h-full space-y-1 text-sm text-white sidenav-shell">
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
            <span class="{{ $iconWrap }}">{!! $menuIcon('login') !!}</span>
            <span>Login</span>
          </a>
        </div>
      @endauth

      <div class="{{ $groupBox }} {{ $lockVisual }}">
        <a href="{{ $href('jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('jobs.*') }}">
          <span class="{{ $iconWrap }}">{!! $menuIcon('jobs') !!}</span>
          <span>Lowongan</span>
        </a>

        @auth
            <a href="{{ $href('applications.mine') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('applications.mine') }}">
              <span class="{{ $iconWrap }}">{!! $menuIcon('applications') !!}</span>
              <span>Lamaran Saya</span>
            </a>

            <a href="{{ $href('me.interviews.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('me.interviews.*') }}">
              <span class="{{ $iconWrap }}">{!! $menuIcon('interviews') !!}</span>
              <span>Interview</span>
            </a>

            @if(in_array($u->role ?? '', ['trainer', 'karyawan']))
              <a href="{{ route('kanban.mine') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('kanban.mine') }}">
                <span class="{{ $iconWrap }}">{!! $menuIcon('kanban') !!}</span>
                <span>Kanban Board</span>
              </a>
            @endif
        @endauth
      </div>

      {{-- ADMIN --}}
      @auth
          @if($hasAdminRole)
              <div class="{{ $groupBox }} {{ $lockVisual }}">
                <a href="{{ $href('dashboard') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('dashboard') }}">
                  <span class="{{ $iconWrap }}">{!! $menuIcon('dashboard') !!}</span>
                  <span>Dashboard</span>
                </a>

                <a href="{{ $href('admin.jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.jobs.*') }}">
                  <span class="{{ $iconWrap }}">{!! $menuIcon('jobs') !!}</span>
                  <span>Jobs</span>
                </a>

                @if (Route::has('admin.candidates.index'))
                      <a href="{{ $href('admin.candidates.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.candidates.*') }}">
                        <span class="{{ $iconWrap }}">{!! $menuIcon('candidates') !!}</span>
                        <span>Candidates</span>
                      </a>
                @endif

                <a href="{{ $href('admin.applications.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.applications.index') }}">
                  <span class="{{ $iconWrap }}">{!! $menuIcon('applications') !!}</span>
                  <span>Applications</span>
                </a>

                <a href="{{ $href('admin.applications.board') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.applications.board') }}">
                  <span class="{{ $iconWrap }}">{!! $menuIcon('kanban') !!}</span>
                  <span>Kanban Board</span>
                </a>

                @if (Route::has('admin.interviews.index'))
                      <a href="{{ $href('admin.interviews.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.interviews.*') }}">
                        <span class="{{ $iconWrap }}">{!! $menuIcon('interviews') !!}</span>
                        <span>Interviews</span>
                      </a>
                @endif

                @if (Route::has('admin.users.index'))
                      <a href="{{ $href('admin.users.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.users.*') }}">
                        <span class="{{ $iconWrap }}">{!! $menuIcon('users') !!}</span>
                        <span>Users</span>
                      </a>
                @endif

                @if ($roleRaw === 'superadmin' && Route::has('admin.audit_logs.index'))
                      <a href="{{ $href('admin.audit_logs.index') }}" {!! $closeAttr !!} class="{{ $linkMobile }} {{ $activeMenu('admin.audit_logs.*') }}">
                        <span class="{{ $iconWrap }}">{!! $menuIcon('audit') !!}</span>
                        <span>Audit Logs</span>
                      </a>
                @endif

              </div>
          @endif
      @endauth

      <div class="flex-1"></div>

    </nav>
@endif
