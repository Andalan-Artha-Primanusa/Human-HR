@component('mail::message')
# Offering Letter – {{ $job->title }}

Halo {{ $candidate->name }},

Selamat! Anda dinyatakan **lulus** untuk posisi **{{ $job->title }}**.

**Ringkasan Penawaran**
- Gaji (gross): Rp {{ number_format($gross,0,',','.') }}
- Tunjangan: Rp {{ number_format($allowance,0,',','.') }}

@isset($offer->body_template)
{!! $offer->body_template !!}
@endisset

@component('mail::button', ['url' => route('admin.offers.pdf', $offer)])
Unduh Offering Letter (PDF)
@endcomponent

Terima kasih,  
HR Team
@endcomponent
