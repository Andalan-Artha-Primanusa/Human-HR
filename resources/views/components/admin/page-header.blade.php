{{-- Shared Page Header — Human.Careers admin design system.
     Solid brown (no gradient, no split two-tone), white text, compact.
     Usage:
     <x-admin.page-header eyebrow="MANPOWER INTELLIGENCE"
                         title="Manpower Dashboard"
                         description="...">
       {{-- content slot: action button(s) and/or meta info, right-aligned --}}
       <a href="{{ route('admin.jobs.create') }}" class="ph-action">...</a>
     </x-admin.page-header>
--}}
@props([
    'eyebrow' => null,
    'title' => '',
    'description' => null,
    'icon' => null,
])
<section {{ $attributes->merge(['class' => 'page-header']) }}>
  <div class="page-header__inner">
    <div class="page-header__copy">
      @if ($eyebrow)
        <p class="page-header__eyebrow">{{ $eyebrow }}</p>
      @endif
      @if ($icon)
        <span class="page-header__icon">{!! $icon !!}</span>
      @endif
      <h1 class="page-header__title">{{ $title }}</h1>
      @if ($description)
        <p class="page-header__desc">{{ $description }}</p>
      @endif
    </div>
    @if (!empty($slot) && trim((string) $slot))
      <div class="page-header__actions">
        {{ $slot }}
      </div>
    @endif
  </div>
</section>