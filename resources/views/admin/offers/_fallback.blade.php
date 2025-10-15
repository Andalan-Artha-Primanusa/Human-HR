{{-- resources/views/offers/_fallback.blade.php --}}
@php
  $app   = $offer->application ?? null;
  $user  = $app?->user?->name ?? '—';
  $title = $app?->job?->title ?? '—';
  $site  = $app?->job?->site?->code ?? '—';
  $gross = number_format((float)($offer->salary['gross'] ?? 0), 0, ',', '.');
  $allow = number_format((float)($offer->salary['allowance'] ?? 0), 0, ',', '.');
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Offering Letter</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#111; font-size:12px; line-height:1.6; }
    .wrap { padding: 28px; }
    h1 { font-size: 20px; margin: 0 0 8px; }
    h2 { font-size: 14px; margin: 16px 0 6px; }
    .meta { margin: 12px 0 16px; color:#555; }
    .table { width:100%; border-collapse: collapse; margin-top: 10px; }
    .table th, .table td { border:1px solid #ddd; padding:8px 10px; }
    .table th { background:#f5f6f8; text-align:left; }
    .muted { color:#666; }
    .sign { margin-top: 40px; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Offering Letter</h1>
    <div class="meta">
      <div><strong>Candidate:</strong> {{ $user }}</div>
      <div><strong>Position:</strong> {{ $title }} @ {{ $site }}</div>
      <div><strong>Offer ID:</strong> #{{ $offer->id }}</div>
      <div><strong>Status:</strong> {{ strtoupper($offer->status ?? 'draft') }}</div>
      <div><strong>Date:</strong> {{ now()->format('d M Y') }}</div>
    </div>

    <h2>Compensation</h2>
    <table class="table">
      <tr>
        <th>Gross Salary</th>
        <td>Rp {{ $gross }}</td>
      </tr>
      <tr>
        <th>Allowance</th>
        <td>Rp {{ $allow }}</td>
      </tr>
    </table>

    @if(!empty($offer->body_template))
      <h2>Notes</h2>
      {!! $offer->body_template !!}
    @endif

    <div class="sign">
      <p class="muted">Regards,</p>
      <p><strong>Human Resources</strong></p>
    </div>
  </div>
</body>
</html>
