{{-- resources/views/layouts/partials/sidenav.blade.php --}}
@php
  // Props (via @include)
  $variant = $variant ?? 'desktop';               // 'desktop' | 'mobile'
  $closeOnClick = $closeOnClick ?? false;         // only used on mobile
  $closeAttr = $closeOnClick ? ' @click="open=false"' : '';

  // Optional: quick link ke Offer PDF (admin.offers.pdf) dengan ID tertentu
  // contoh include: @include('layouts.partials.sidenav', ['offerQuickId' => $latestOfferId ?? null])
  $offerQuickId = $offerQuickId ?? null;

  // Active states per-warna (hindari dynamic class Tailwind)
  $activeBlue = function ($pattern) {
      return request()->routeIs($pattern) ? 'bg-blue-50 text-blue-700 font-semibold' : '';
  };
  $activeRed = function ($pattern) {
      return request()->routeIs($pattern) ? 'bg-red-50 text-red-700 font-semibold' : '';
  };

  // Link styles per-warna
  $linkDeskBlue    = 'flex items-center gap-2 px-3 py-2 rounded-lg text-blue-700 hover:bg-blue-50';
  $linkDeskRed     = 'flex items-center gap-2 px-3 py-2 rounded-lg text-red-700 hover:bg-red-50';
  $linkMobileBlue  = 'block px-3 py-2 rounded-lg text-blue-700 hover:bg-blue-50';
  $linkMobileRed   = 'block px-3 py-2 rounded-lg text-red-700 hover:bg-red-50';
@endphp

@if($variant === 'desktop')
  <nav class="p-3 space-y-1 text-sm">
    <div class="px-3 pt-3 pb-1 text-xs font-semibold text-blue-600 uppercase">General</div>

    <a href="{{ route('jobs.index') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('jobs.*') }}">
      {{-- briefcase icon --}}
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z"/>
      </svg>
      Lowongan
    </a>

    @auth
      <a href="{{ route('dashboard') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('dashboard') }}">
        {{-- squares-2x2 icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h6.5v6.5h-6.5zM13.75 4.5h6.5v6.5h-6.5zM3.75 14.5h6.5v6.5h-6.5zM13.75 14.5h6.5v6.5h-6.5z"/>
        </svg>
        Dashboard
      </a>

      <a href="{{ route('applications.mine') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('applications.mine') }}">
        {{-- clipboard-document-list icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 0 0-2 2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3a2 2 0 0 0-2-2Z"/>
        </svg>
        Lamaran Saya
      </a>

      <a href="{{ route('profile.edit') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('profile.edit') }}">
        {{-- user icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a7.5 7.5 0 0 1 15 0"/>
        </svg>
        Profil
      </a>
    @endauth

    @auth
      @if(in_array(auth()->user()->role ?? 'pelamar', ['hr','superadmin']))
        <div class="px-3 pt-4 pb-1 text-xs font-semibold text-red-600 uppercase">Admin</div>

        {{-- Admin: Sites (resource) --}}
        @if (Route::has('admin.sites.index'))
          <a href="{{ route('admin.sites.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.sites.*') }}">
            {{-- building-office icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V5a2 2 0 0 1 2-2h5v18m7 0V9a2 2 0 0 0-2-2h-5"/>
            </svg>
            Sites
          </a>
        @endif

        <a href="{{ route('admin.jobs.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.jobs.*') }}">
          {{-- briefcase icon --}}
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z"/>
          </svg>
          Jobs
        </a>

        <a href="{{ route('admin.applications.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.applications.index') }}">
          {{-- clipboard list icon --}}
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 0 0-2 2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3a2 2 0 0 0-2-2Z"/>
          </svg>
          Applications
        </a>

        <a href="{{ route('admin.applications.board') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.applications.board') }}">
          {{-- view-columns/kanban icon --}}
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z"/>
          </svg>
          Kanban Board
        </a>

        {{-- Admin: Interviews, Psychotests, Offers --}}
        @if (Route::has('admin.interviews.index'))
          <a href="{{ route('admin.interviews.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.interviews.*') }}">
            {{-- chat-bubble-left-right icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 0 1 8.75 6.5h6.5A6.25 6.25 0 0 1 21.5 12.75v.25a4.75 4.75 0 0 1-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 0 1 4 13v-.25Z"/>
            </svg>
            Interviews
          </a>
        @endif

        @if (Route::has('admin.psychotests.index'))
          <a href="{{ route('admin.psychotests.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.psychotests.*') }}">
            {{-- beaker icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 2.5v4l-5.5 9A3 3 0 0 0 6 20.5h12a3 3 0 0 0 2.5-5l-5.5-9v-4"/>
            </svg>
            Psychotests
          </a>
        @endif

        @if (Route::has('admin.offers.index'))
          <a href="{{ route('admin.offers.index') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.offers.*') }}">
            {{-- document-text icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 0 0 4.5 4v16A2.5 2.5 0 0 0 7 22.5h10A2.5 2.5 0 0 0 19.5 20V7.5Z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 13h7M8.5 16h7M8.5 10h4"/>
            </svg>
            Offers
          </a>
        @endif

        {{-- Quick Offer PDF (opsional dengan $offerQuickId) --}}
        @if ($offerQuickId && Route::has('admin.offers.pdf'))
          <a href="{{ route('admin.offers.pdf', $offerQuickId) }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.offers.pdf') }}">
            {{-- document-arrow-down icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 12 15.75l3-3m-3-10.5v10.5M6 19.5h12M19.5 7.5 13.5 1.5"/>
            </svg>
            Offer PDF (quick)
          </a>
        @endif

        <a href="{{ route('admin.dashboard.manpower') }}" class="{{ $linkDeskRed }} {{ $activeRed('admin.dashboard.manpower') }}">
          {{-- chart-bar icon --}}
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5h18M6 17V9m6 8V5m6 12v-6"/>
          </svg>
          Manpower Dashboard
        </a>
      @endif
    @endauth

    <div class="px-3 pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase">Account</div>
    @auth
      <form method="POST" action="{{ route('logout') }}" class="px-3 py-2">
        @csrf
        <button class="w-full btn btn-accent">
          {{-- arrow-right-on-rectangle icon --}}
          <span class="inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3H6.75A2.25 2.25 0 0 0 4.5 5.25v13.5A2.25 2.25 0 0 0 6.75 21H13.5a2.25 2.25 0 0 0 2.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3"/>
            </svg>
            Logout
          </span>
        </button>
      </form>
    @else
      <a href="{{ route('login') }}" class="{{ $linkDeskBlue }} {{ $activeBlue('login') }}">
        {{-- arrow-left-on-rectangle icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15V18.75A2.25 2.25 0 0 0 10.5 21h6.75A2.25 2.25 0 0 0 19.5 18.75v-13.5A2.25 2.25 0 0 0 17.25 3H10.5A2.25 2.25 0 0 0 8.25 5.25V9M15 12H3m0 0 3-3m-3 3 3 3"/>
        </svg>
        Login
      </a>
    @endauth
  </nav>
@else
  <nav class="space-y-1 text-sm">
    <a href="{{ route('jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('jobs.*') }}">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z"/>
      </svg>
      Lowongan
    </a>

    @auth
      <a href="{{ route('dashboard') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('dashboard') }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h6.5v6.5h-6.5zM13.75 4.5h6.5v6.5h-6.5zM3.75 14.5h6.5v6.5h-6.5zM13.75 14.5h6.5v6.5h-6.5z"/>
        </svg>
        Dashboard
      </a>

      <a href="{{ route('applications.mine') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('applications.mine') }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 0 0-2 2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3a2 2 0 0 0-2-2Z"/>
        </svg>
        Lamaran Saya
      </a>

      <a href="{{ route('profile.edit') }}" {!! $closeAttr !!} class="{{ $linkMobileBlue }} {{ $activeBlue('profile.edit') }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a7.5 7.5 0 0 1 15 0"/>
        </svg>
        Profil
      </a>

      @if(in_array(auth()->user()->role ?? 'pelamar', ['hr','superadmin']))
        <div class="px-3 pt-3 pb-1 text-xs font-semibold text-red-600 uppercase">Admin</div>

        @if (Route::has('admin.sites.index'))
          <a href="{{ route('admin.sites.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.sites.*') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V5a2 2 0 0 1 2-2h5v18m7 0V9a2 2 0 0 0-2-2h-5"/>
            </svg>
            Sites
          </a>
        @endif

        <a href="{{ route('admin.jobs.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.jobs.*') }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z"/>
          </svg>
          Jobs
        </a>

        <a href="{{ route('admin.applications.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.applications.index') }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 0 0-2 2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3a2 2 0 0 0-2-2Z"/>
          </svg>
          Applications
        </a>

        <a href="{{ route('admin.applications.board') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.applications.board') }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5zM9.75 5.25h4.5v13.5h-4.5zM15.75 5.25h4.5v13.5h-4.5z"/>
          </svg>
          Kanban Board
        </a>

        @if (Route::has('admin.interviews.index'))
          <a href="{{ route('admin.interviews.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.interviews.*') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12.75A6.25 6.25 0 0 1 8.75 6.5h6.5A6.25 6.25 0 0 1 21.5 12.75v.25a4.75 4.75 0 0 1-4.75 4.75h-3L9 21.5l.75-3.75H8.75A4.75 4.75 0 0 1 4 13v-.25Z"/>
            </svg>
            Interviews
          </a>
        @endif

        @if (Route::has('admin.psychotests.index'))
          <a href="{{ route('admin.psychotests.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.psychotests.*') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 2.5v4l-5.5 9A3 3 0 0 0 6 20.5h12a3 3 0 0 0 2.5-5l-5.5-9v-4"/>
            </svg>
            Psychotests
          </a>
        @endif

        @if (Route::has('admin.offers.index'))
          <a href="{{ route('admin.offers.index') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.offers.*') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 0 0 4.5 4v16A2.5 2.5 0 0 0 7 22.5h10A2.5 2.5 0 0 0 19.5 20V7.5Z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 13h7M8.5 16h7M8.5 10h4"/>
            </svg>
            Offers
          </a>
        @endif

        @if ($offerQuickId && Route::has('admin.offers.pdf'))
          <a href="{{ route('admin.offers.pdf', $offerQuickId) }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.offers.pdf') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 12 15.75l3-3m-3-10.5v10.5M6 19.5h12M19.5 7.5 13.5 1.5"/>
            </svg>
            Offer PDF (quick)
          </a>
        @endif

        <a href="{{ route('admin.dashboard.manpower') }}" {!! $closeAttr !!} class="{{ $linkMobileRed }} {{ $activeRed('admin.dashboard.manpower') }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5h18M6 17V9m6 8V5m6 12v-6"/>
          </svg>
          Manpower Dashboard
        </a>
      @endif
    @endauth

    <div class="pt-4 mt-4 border-t border-slate-200">
      @auth
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button class="w-full btn btn-accent">
            <span class="inline-flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3H6.75A2.25 2.25 0 0 0 4.5 5.25v13.5A2.25 2.25 0 0 0 6.75 21H13.5a2.25 2.25 0 0 0 2.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3"/>
              </svg>
              Logout
            </span>
          </button>
        </form>
      @else
        <a href="{{ route('login') }}" class="{{ $linkMobileBlue }} {{ $activeBlue('login') }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15V18.75A2.25 2.25 0 0 0 10.5 21h6.75A2.25 2.25 0 0 0 19.5 18.75v-13.5A2.25 2.25 0 0 0 17.25 3H10.5A2.25 2.25 0 0 0 8.25 5.25V9M15 12H3m0 0 3-3m-3 3 3 3"/>
          </svg>
          Login
        </a>
      @endauth
    </div>
  </nav>
@endif
