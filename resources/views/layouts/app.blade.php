<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Careers') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body x-data="{ open:false }" class="min-h-screen bg-slate-50 font-sans antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">

      {{-- SIDEBAR (desktop) --}}
      <aside class="hidden lg:flex lg:flex-col bg-white/90 backdrop-blur border-r border-slate-200">
        <div class="h-14 px-4 flex items-center gap-2 border-b border-slate-200">
          <div class="h-8 w-8 rounded-xl" style="background: var(--primary);"></div>
          <a href="{{ url('/') }}" class="font-semibold text-slate-900">{{ config('app.name','Careers') }}</a>
        </div>
        @include('layouts.sidenav', ['variant' => 'desktop'])
      </aside>

      {{-- DRAWER (mobile) --}}
      <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/30 lg:hidden" @click="open=false"></div>
      <aside x-show="open" x-transition
             class="fixed inset-y-0 left-0 z-50 w-[260px] bg-white border-r border-slate-200 p-3 overflow-y-auto lg:hidden">
        <div class="h-10 mb-2 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="h-7 w-7 rounded-xl" style="background: var(--primary);"></div>
            <span class="font-semibold text-slate-900">{{ config('app.name','Careers') }}</span>
          </div>
          <button class="btn btn-ghost" @click="open=false">✕</button>
        </div>

        @include('layouts.sidenav', ['variant' => 'mobile', 'closeOnClick' => true])
      </aside>

      {{-- AREA KONTEN --}}
      <section class="flex flex-col min-h-screen">
        {{-- TOPBAR --}}
        <header class="h-14 border-b border-slate-200 bg-white/70 backdrop-blur sticky top-0 z-30">
          <div class="container-page h-full flex items-center justify-between">
            <div class="flex items-center gap-3">
              <button class="btn btn-outline lg:hidden" @click="open=true">☰</button>
              <a href="{{ url('/') }}" class="hidden lg:flex items-center gap-2">
                <div class="h-6 w-6 rounded-md" style="background: var(--primary);"></div>
                <span class="font-semibold text-slate-900">{{ config('app.name','Careers') }}</span>
              </a>
            </div>
            <div class="flex items-center gap-3">
              @auth
                <span class="hidden sm:inline text-sm text-slate-600">Hi, {{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf
                  <button class="btn btn-accent">Logout</button>
                </form>
              @else
                <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
              @endauth
            </div>
          </div>
        </header>

        {{-- FLASH --}}
        @if(session('ok') || session('warn'))
          <div class="container-page mt-4">
            @if(session('ok'))  <div class="card"><div class="card-body text-emerald-700">✅ {{ session('ok') }}</div></div>@endif
            @if(session('warn')) <div class="card"><div class="card-body text-amber-700">⚠️ {{ session('warn') }}</div></div>@endif
          </div>
        @endif

        {{-- HERO (opsional) --}}
        @isset($header)
          <div class="bg-white/60">
            <div class="container-page py-6">
              <div class="hero rounded-2xl p-6">
                {{ $header }}
              </div>
            </div>
          </div>
        @endisset

        {{-- CONTENT --}}
        <main class="container-page py-8 flex-1">
          {{ $slot ?? '' }}
          @yield('content')
        </main>

        <footer class="border-t border-slate-200 py-8 text-center text-sm text-slate-500">
          © {{ date('Y') }} {{ config('app.name','Careers') }}
        </footer>
      </section>
    </div>

    {{-- Alpine --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  </body>
</html>
