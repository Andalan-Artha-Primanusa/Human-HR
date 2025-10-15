@php
  $app   = $offer->application ?? null;
  $user  = $app?->user?->name ?? '—';
  $title = $app?->job?->title ?? '—';
  $site  = $app?->job?->site?->code ?? '—';
  $gross = number_format((float)($offer->salary['gross'] ?? 0), 0, ',', '.');
  $allow = number_format((float)($offer->salary['allowance'] ?? 0), 0, ',', '.');

  $badge = match($offer->status){
    'accepted' => 'badge-green',
    'rejected' => 'badge-rose',
    'sent'     => 'badge-blue',
    default    => 'badge-amber',
  };
@endphp

<tr class="align-top">
  <td class="td font-medium text-slate-900">{{ $user }}</td>
  <td class="td">{{ $title }}</td>
  <td class="td">{{ $site }}</td>
  <td class="td text-center">Rp {{ $gross }}</td>
  <td class="td text-center">Rp {{ $allow }}</td>
  <td class="td text-center">
    <span class="badge {{ $badge }}">{{ strtoupper($offer->status ?? 'draft') }}</span>
  </td>
  <td class="td text-right">
    <div class="flex justify-end gap-2">
      @if(Route::has('admin.offers.pdf'))
        <a class="btn btn-outline btn-sm" href="{{ route('admin.offers.pdf', $offer) }}">PDF</a>
      @endif
    </div>
  </td>
</tr>
