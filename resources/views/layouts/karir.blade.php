<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Careers') }}</title>
    @php
  $manifestFile = public_path('build/manifest.json');
  $manifest = is_file($manifestFile) ? json_decode(file_get_contents($manifestFile), true) : [];

  // entry utama sesuai vite.config (umumnya resources/js/app.js)
  $entry = $manifest['resources/js/app.js'] ?? null;
@endphp

@if($entry)
  {{-- CSS yang dibundel --}}
  @foreach(($entry['css'] ?? []) as $css)
    <link rel="stylesheet" href="{{ asset('build/'.$css) }}">
  @endforeach

  {{-- JS (module) --}}
  <script type="module" src="{{ asset('build/'.$entry['file']) }}"></script>
@else
  {{-- Fallback aman kalau manifest belum ada/terupload --}}
  <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
  <script type="module" src="{{ asset('build/assets/app.js') }}"></script>
@endif
</head>

<body class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white/70 backdrop-blur sticky top-0 z-40">
        <div class="container-page flex items-center justify-between h-14">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-xl" style="background: var(--primary);"></div>
                <span class="font-semibold text-slate-900">{{ config('app.name', 'Careers') }}</span>
            </a>
            <nav class="hidden md:flex items-center gap-6">
                <a class="nav-link" href="{{ route('jobs.index') }}">Lowongan</a>
                @auth
                <a class="nav-link" href="{{ route('applications.mine') }}">Lamaran Saya</a>
                @role('hr|superadmin')
                <a class="nav-link" href="{{ route('admin.jobs.index') }}">Admin</a>
                @endrole
                @endauth
            </nav>
            <div class="flex items-center gap-3">
                @auth
                <a href="{{ route('profile.edit') }}" class="btn btn-ghost">Profil</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-accent">Logout</button></form>
                @else
                <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                @endauth
            </div>
        </div>
    </header>


    {{-- Flash messages --}}
    @if(session('ok') || session('warn'))
    <div class="container-page mt-4">
        @if(session('ok'))<div class="card">
            <div class="card-body text-emerald-700">✅ {{ session('ok') }}</div>
        </div>@endif
        @if(session('warn'))<div class="card">
            <div class="card-body text-amber-700">⚠️ {{ session('warn') }}</div>
        </div>@endif
    </div>
    @endif


    <main class="container-page py-8">
        @isset($header)
        <div class="mb-6 hero rounded-2xl p-6">
            <h1 class="text-xl md:text-2xl font-semibold text-slate-900">{{ $header }}</h1>
            @isset($sub)
            <p class="mt-1 text-slate-600">{{ $sub }}</p>
            @endisset
        </div>
        @endisset


        {{ $slot ?? '' }}
        @yield('content')
    </main>


    <footer class="mt-10 border-t border-slate-200 py-8 text-center text-sm text-slate-500">
        © {{ date('Y') }} {{ config('app.name','Careers') }} · Built with ❤️
    </footer>


    {{-- Alpine & Chart.js (CDN for simplicity) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</body>

</html>