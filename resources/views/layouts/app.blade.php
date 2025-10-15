<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', config('app.name'))</title>

  @vite(['resources/css/app.css','resources/js/app.js'])

  {{-- Pakai salah satu: jika Alpine belum diimport di app.js, nyalakan CDN; kalau sudah, hapus baris ini. --}}
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
   [x-cloak]{display:none!important}

  /* ==== MINI MODE (desktop) ==== */
  @media (min-width: 768px){
    aside[data-mini="true"] nav a{ justify-content:center; }
    aside[data-mini="true"] nav a .label{ display:none; }
    aside[data-mini="true"] .section-title{ display:none; }
    aside[data-mini="true"] .brand-text{ display:none; }

    /* Tombol Logout saat mini: icon-only, square */
    aside[data-mini="true"] form .btn{
      width:44px; height:44px; padding:0;
      border-radius:0.75rem;
      display:flex; align-items:center; justify-content:center;
    }
    aside[data-mini="true"] form .btn > span{ gap:0; }
    aside[data-mini="true"] form .btn .label{ display:none; }
  }
  </style>
</head>
<body class="h-full bg-slate-50 text-slate-800">

<div
  x-data="{ open:false, sideMode:'full' }"
  x-init="try{ sideMode = JSON.parse(localStorage.getItem('ac.sideMode') ?? JSON.stringify('full')) }catch(e){}"
  x-effect="localStorage.setItem('ac.sideMode', JSON.stringify(sideMode))"
  x-cloak
  x-id="['drawer']"
  x-trap.noscroll="open"
  @keydown.window.escape="open=false"
  class="min-h-screen flex">

  {{-- ===== Desktop Sidebar (>= md) ===== --}}
  <aside
    class="hidden md:flex md:flex-col border-r border-slate-200 bg-white transition-all duration-200"
    :class="sideMode === 'mini' ? 'md:w-20' : 'md:w-64'"
    :data-mini="(sideMode==='mini')">
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
    <div x-show="open" x-transition.opacity @click="open=false" class="fixed inset-0 bg-black/40 z-40"></div>

    {{-- Panel --}}
    <aside
      x-show="open"
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="-translate-x-full opacity-0"
      x-transition:enter-end="translate-x-0 opacity-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="translate-x-0 opacity-100"
      x-transition:leave-end="-translate-x-full opacity-0"
      :id="$id('drawer')"
      class="fixed inset-y-0 left-0 z-50 w-72 max-w-[80vw] bg-white border-r border-slate-200 shadow-xl flex flex-col">
      <div class="h-14 flex items-center justify-between px-4 border-b border-slate-200">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 via-teal-500 to-sky-500"></div>
          <div class="font-semibold">Human.Careers</div>
        </div>
        <button @click="open=false" class="p-2 rounded-lg hover:bg-slate-100" aria-label="Tutup menu">
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
      <button class="md:hidden p-2 rounded-lg hover:bg-slate-100" @click="open=true" :aria-controls="$id('drawer')" aria-label="Buka menu">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
      </button>

      {{-- Desktop collapse/expand --}}
      <button
        class="hidden md:inline-flex p-2 rounded-lg hover:bg-slate-100"
        @click="sideMode = (sideMode === 'mini' ? 'full' : 'mini')"
        :aria-label="sideMode==='mini' ? 'Perluas sidebar' : 'Kecilkan sidebar'">
        <svg x-show="sideMode==='mini'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          {{-- icon expand --}}
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M4 6h10M4 18h10"/>
        </svg>
        <svg x-show="sideMode==='full'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          {{-- icon collapse --}}
          <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4m10 6H4m10-12H4"/>
        </svg>
      </button>

      <div class="font-semibold">@yield('title','Dashboard')</div>
      <div class="ml-auto"></div>
    </header>

    <main class="p-3 md:p-6">@yield('content')</main>
  </div>
</div>
</body>
</html>
