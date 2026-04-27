<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'karir-andalan')</title>
  @stack('head')

  <style>
    [data-cloak]{ visibility: hidden !important; }

    @media (min-width: 768px){
      aside.is-mini nav a{ justify-content:center; }
      aside.is-mini nav a .label{ display:none; }
      aside.is-mini .section-title{ display:none; }
      aside.is-mini .brand-text{ display:none; }
      aside.is-mini .logo-wrap{ justify-content:center; padding-left:.5rem; padding-right:.5rem; }
      aside.is-mini .logo-img{ max-width:100% !important; }

      /* Tombol Logout saat mini: icon-only, square */
      aside.is-mini form .btn{
        width:44px; height:44px; padding:0;
        border-radius:0.75rem;
        display:flex; align-items:center; justify-content:center;
      }
      aside.is-mini form .btn > span{ gap:0; }
      aside.is-mini form .btn .label{ display:none; }
    }

    /* Drawer helpers (tanpa Alpine) */
    .drawer-overlay{ display:none; }
    .drawer-panel{ transform:translateX(-100%); opacity:0; pointer-events:none; }
    .drawer-open .drawer-overlay{ display:block; }
    .drawer-open .drawer-panel{ transform:translateX(0); opacity:1; pointer-events:auto; }
    .no-scroll{ overflow:hidden; }
    .transition-base{ transition: transform .2s ease, opacity .15s ease; }
  </style>
</head>
<body class="text-slate-800">

@if (session('verify_email_notice'))
  <div id="verifyEmailModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/50" role="dialog" aria-modal="true" aria-labelledby="verifyEmailModalTitle">
    <div class="w-full max-w-sm p-6 bg-white shadow-2xl rounded-xl ring-1 ring-slate-200">
      <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-amber-100 text-amber-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16v12H4z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 7 8 6 8-6" />
        </svg>
      </div>
      <h2 id="verifyEmailModalTitle" class="text-lg font-semibold text-center text-slate-900">Cek email kamu</h2>
      <p class="mt-2 text-sm leading-6 text-center text-slate-600">{{ session('verify_email_notice') }}</p>
      <button type="button" id="verifyEmailModalClose" class="mt-5 w-full rounded-lg bg-[#a77d52] px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90">
        Oke
      </button>
    </div>
  </div>
@endif

<div id="appRoot" class="flex min-h-screen">
  <aside
    id="desktopSidebar"
    data-cloak
    class="hidden transition-all duration-200 bg-white border-r md:flex md:flex-col border-slate-200 md:w-64">
    <div class="flex-1 overflow-y-auto">
      @include('layouts.sidenav', [
        'variant' => 'desktop',
        'closeOnClick' => false,
        'offerQuickId' => $offerQuickId ?? null
      ])
    </div>
  </aside>
  <div class="md:hidden">
    <div id="drawerOverlay" class="fixed inset-0 z-40 drawer-overlay bg-black/40" aria-hidden="true"></div>

    {{-- Panel --}}
    <aside
      id="mobileDrawer"
      data-cloak
      class="drawer-panel transition-base fixed inset-y-0 left-0 z-50 w-72 max-w-[80vw] bg-white border-r border-slate-200 shadow-xl flex flex-col"
      role="dialog" aria-modal="true" aria-label="Menu">
      <div class="flex items-center justify-center w-full h-full min-h-screen overflow-hidden" style="margin:0;padding:0;">
        <div class="flex items-center gap-2">
        </div>
        <button id="drawerCloseBtn" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600" aria-label="Tutup menu">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="flex-1 p-2 overflow-y-auto">
        @include('layouts.sidenav', [
            'variant' => 'mobile',
            'closeOnClick' => true,
            'offerQuickId' => $offerQuickId ?? null
        ])
      </div>
    </aside>
  </div>

  {{-- ===== Main ===== --}}
  <div class="flex flex-col flex-1 min-w-0">
    <header class="sticky top-0 z-30 flex items-center gap-2 px-3 bg-white border-b h-14 border-slate-200 md:px-5">
      <button id="drawerOpenBtn" class="p-2 rounded-lg md:hidden hover:bg-slate-100" aria-label="Buka menu">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
        </svg>
      </button>

      {{-- Desktop collapse/expand --}}
      <button id="toggleSidebarBtn" class="hidden p-2 rounded-lg md:inline-flex hover:bg-slate-100" aria-label="Ubah ukuran sidebar" aria-pressed="false">
        <svg id="iconExpand" xmlns="http://www.w3.org/2000/svg" class="hidden w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M4 6h10M4 18h10"/>
        </svg>
        <svg id="iconCollapse" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4m10 6H4m10-12H4"/>
        </svg>
      </button>

      <div class="font-semibold">@yield('title', 'Dashboard')</div>
      @include('layouts.partials.topbar-actions')
    </header>

    <main class="p-3 md:p-6">@yield('content')</main>
  </div>
</div>

<script>
  (function(){
    const $doc    = document;
    const $aside  = $doc.getElementById('desktopSidebar');
    const $drawer = $doc.getElementById('mobileDrawer');
    const $btnTog = $doc.getElementById('toggleSidebarBtn');
    const $burger = $doc.getElementById('drawerOpenBtn');
    const $close  = $doc.getElementById('drawerCloseBtn');
    const $panel  = $doc.getElementById('mobileDrawer');
    const $ovl    = $doc.getElementById('drawerOverlay');
    const $iconExpand   = $doc.getElementById('iconExpand');
    const $iconCollapse = $doc.getElementById('iconCollapse');

    // --- Side mode (mini/full) persist ---
    const LS_KEY = 'ac.sideMode';
    let sideMode = 'full';
    try{
      const raw = localStorage.getItem(LS_KEY);
      if(raw) sideMode = JSON.parse(raw);
    }catch(e){/* ignore */}

    function applySideMode(){
      if(!$aside) return;
      const isDesktop = window.matchMedia('(min-width: 768px)').matches;
      if(!isDesktop) return;

      if(sideMode === 'mini'){
        $aside.classList.add('is-mini');
        $aside.classList.remove('md:w-64');
        $aside.classList.add('md:w-20');
        if($iconExpand)   $iconExpand.classList.remove('hidden');
        if($iconCollapse) $iconCollapse.classList.add('hidden');
        if($btnTog) $btnTog.setAttribute('aria-pressed','true');
      }else{
        $aside.classList.remove('is-mini');
        $aside.classList.add('md:w-64');
        $aside.classList.remove('md:w-20');
        if($iconExpand)   $iconExpand.classList.add('hidden');
        if($iconCollapse) $iconCollapse.classList.remove('hidden');
        if($btnTog) $btnTog.setAttribute('aria-pressed','false');
      }
      try{ localStorage.setItem(LS_KEY, JSON.stringify(sideMode)); }catch(e){}
    }

    applySideMode();

    // Lepas cloak setelah sidebar dikonfigurasi
    if($aside)  $aside.removeAttribute('data-cloak');
    if($drawer) $drawer.removeAttribute('data-cloak');

    if($btnTog){
      $btnTog.addEventListener('click', () => {
        sideMode = (sideMode === 'mini') ? 'full' : 'mini';
        applySideMode();
      }, { passive:true });
    }

    // --- Drawer open/close (mobile) ---
    function openDrawer(){
      $doc.documentElement.classList.add('drawer-open');
      document.body.classList.add('no-scroll');
      if($panel) $panel.setAttribute('aria-hidden','false');
    }
    function closeDrawer(){
      $doc.documentElement.classList.remove('drawer-open');
      document.body.classList.remove('no-scroll');
      if($panel) $panel.setAttribute('aria-hidden','true');
    }

    $burger && $burger.addEventListener('click', openDrawer,  { passive:true });
    $close  && $close.addEventListener('click',  closeDrawer, { passive:true });
    $ovl    && $ovl.addEventListener('click',    closeDrawer, { passive:true });

    $doc.addEventListener('keydown', (e) => {
      if(e.key === 'Escape'){ closeDrawer(); }
    });

    const verifyModal = $doc.getElementById('verifyEmailModal');
    const verifyClose = $doc.getElementById('verifyEmailModalClose');
    verifyClose && verifyClose.addEventListener('click', () => {
      verifyModal && verifyModal.remove();
    });
  })();
</script>

@auth
  @push('scripts')
    <script>
      (function(){
        const url = @json(route('me.notifications.index', ['format' => 'json']));
        fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } })
          .then(r => (r.ok ? r.json() : null))
          .then(json => {  })
          .catch(() => {});
      })();
    </script>
  @endpush
@endauth

@stack('scripts')
</body>
</html>