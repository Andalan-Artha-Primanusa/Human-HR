{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', config('app.name'))</title>

  @vite(['resources/css/app.css','resources/js/app.js'])

  <style>
    [data-cloak]{display:none!important}

    /* ==== MINI MODE (desktop) ==== */
    @media (min-width: 768px){
      aside.is-mini nav a{ justify-content:center; }
      aside.is-mini nav a .label{ display:none; }
      aside.is-mini .section-title{ display:none; }
      aside.is-mini .brand-text{ display:none; }

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
<body class="h-full bg-slate-50 text-slate-800">

<div id="appRoot" class="min-h-screen flex" data-cloak>
  {{-- ===== Desktop Sidebar (>= md) ===== --}}
  <aside
    id="desktopSidebar"
    class="hidden md:flex md:flex-col border-r border-slate-200 bg-white transition-all duration-200 md:w-64">
    <div class="h-14 flex items-center gap-2 px-4 border-b border-slate-200">
      <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 via-teal-500 to-sky-500"></div>
      <div class="font-semibold brand-text">Human.Careers</div>
    </div>
    <div class="flex-1 overflow-y-auto">
      @include('layouts.sidenav', [
        'variant' => 'desktop',
        'closeOnClick' => false,
        'offerQuickId' => $offerQuickId ?? null
      ])
    </div>
  </aside>

  {{-- ===== Mobile Drawer (< md) ===== --}}
  <div class="md:hidden">
    {{-- Overlay --}}
    <div id="drawerOverlay" class="drawer-overlay fixed inset-0 bg-black/40 z-40"></div>

    {{-- Panel --}}
    <aside
      id="mobileDrawer"
      class="drawer-panel transition-base fixed inset-y-0 left-0 z-50 w-72 max-w-[80vw] bg-white border-r border-slate-200 shadow-xl flex flex-col">
      <div class="h-14 flex items-center justify-between px-4 border-b border-slate-200">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 via-teal-500 to-sky-500"></div>
          <div class="font-semibold">Human.Careers</div>
        </div>
        <button id="drawerCloseBtn" class="p-2 rounded-lg hover:bg-slate-100" aria-label="Tutup menu">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="flex-1 overflow-y-auto p-2">
        @include('layouts.sidenav', [
          'variant' => 'mobile',
          'closeOnClick' => true,
          'offerQuickId' => $offerQuickId ?? null
        ])
      </div>
    </aside>
  </div>

  {{-- ===== Main ===== --}}
  <div class="flex-1 flex flex-col min-w-0">
    <header class="h-14 sticky top-0 z-30 bg-white border-b border-slate-200 flex items-center px-3 md:px-5 gap-2">
      {{-- Burger (mobile) --}}
      <button id="drawerOpenBtn" class="md:hidden p-2 rounded-lg hover:bg-slate-100" aria-label="Buka menu">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
        </svg>
      </button>

      {{-- Desktop collapse/expand --}}
      <button id="toggleSidebarBtn" class="hidden md:inline-flex p-2 rounded-lg hover:bg-slate-100" aria-label="Toggle sidebar"></button>
      <script>
        (function(btn){
          if(!btn) return;
          btn.innerHTML = `
            <svg id="iconExpand" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M4 6h10M4 18h10"/>
            </svg>
            <svg id="iconCollapse" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4m10 6H4m10-12H4"/>
            </svg>`;
        })(document.getElementById('toggleSidebarBtn'));
      </script>

      <div class="font-semibold">@yield('title','Dashboard')</div>

      {{-- === Right actions (dipisah ke partial) === --}}
      @include('layouts.partials.topbar-actions')
    </header>

    <main class="p-3 md:p-6">@yield('content')</main>
  </div>
</div>

<script>
  (function(){
    const $root   = document.getElementById('appRoot')
    const $aside  = document.getElementById('desktopSidebar')
    const $btnTog = document.getElementById('toggleSidebarBtn')
    const $burger = document.getElementById('drawerOpenBtn')
    const $close  = document.getElementById('drawerCloseBtn')
    const $panel  = document.getElementById('mobileDrawer')
    const $ovl    = document.getElementById('drawerOverlay')
    const iconExpand  = () => document.getElementById('iconExpand')
    const iconCollapse= () => document.getElementById('iconCollapse')

    // --- Side mode (mini/full) persist ---
    const LS_KEY = 'ac.sideMode'
    let sideMode = 'full'
    try{
      const raw = localStorage.getItem(LS_KEY)
      if(raw) sideMode = JSON.parse(raw)
    }catch(e){}

    const applySideMode = () => {
      if(window.matchMedia('(min-width: 768px)').matches){
        if(sideMode === 'mini'){
          $aside.classList.add('is-mini')
          $aside.classList.remove('md:w-64')
          $aside.classList.add('md:w-20')
          if(iconExpand())  iconExpand().classList.add('hidden')
          if(iconCollapse())iconCollapse().classList.remove('hidden')
          if(iconExpand() && iconCollapse()){
            iconExpand().classList.remove('hidden')
            iconCollapse().classList.add('hidden')
          }
        } else {
          $aside.classList.remove('is-mini')
          $aside.classList.add('md:w-64')
          $aside.classList.remove('md:w-20')
          if(iconExpand() && iconCollapse()){
            iconExpand().classList.add('hidden')
            iconCollapse().classList.remove('hidden')
          }
        }
      }
      try{ localStorage.setItem(LS_KEY, JSON.stringify(sideMode)) }catch(e){}
    }
    applySideMode()

    if($btnTog){
      $btnTog.addEventListener('click', () => {
        sideMode = (sideMode === 'mini' ? 'full' : 'mini')
        applySideMode()
      })
    }

    // --- Drawer open/close (mobile) ---
    const openDrawer  = () => {
      document.documentElement.classList.add('drawer-open')
      document.body.classList.add('no-scroll')
    }
    const closeDrawer = () => {
      document.documentElement.classList.remove('drawer-open')
      document.body.classList.remove('no-scroll')
    }

    $burger && $burger.addEventListener('click', openDrawer)
    $close  && $close.addEventListener('click', closeDrawer)
    $ovl    && $ovl.addEventListener('click', closeDrawer)

    document.addEventListener('keydown', (e) => {
      if(e.key === 'Escape'){ closeDrawer() }
    })

    $root && $root.removeAttribute('data-cloak')
  })();
</script>
</body>
</html>
