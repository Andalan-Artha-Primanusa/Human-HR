{{-- resources/views/components/section-title.blade.php --}}
{{-- Anonymous component: judul section card dengan aksen ikon coklat. Props: title, badge (opt). --}}
@props(['title' => '', 'badge' => null, 'icon' => null])

<div class="flex items-center justify-between gap-3">
  <div class="flex items-center gap-3">
    @if($icon)
      <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-[#fdf7f0] text-[#a77d52]">
        {!! $icon !!}
      </span>
    @else
      <span class="h-5 w-1 shrink-0 rounded-full bg-[#a77d52]"></span>
    @endif
    <h2 class="text-base font-bold text-[#5c3d1e]">{{ $title }}</h2>
  </div>
  @if($badge !== null)
    <span class="rounded-full bg-[#fdf7f0] px-2.5 py-1 text-xs font-semibold text-[#a77d52]">{{ $badge }}</span>
  @endif
</div>