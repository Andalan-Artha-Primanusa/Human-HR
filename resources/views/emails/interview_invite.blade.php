@component('mail::message')
# Undangan Interview: {{ $interview->title }}

Halo {{ $candidate->name }},

Anda dijadwalkan untuk interview pada lowongan **{{ $job->title }}**.

**Waktu**: {{ \Carbon\Carbon::parse($interview->start_at)->format('d M Y H:i') }} – {{ \Carbon\Carbon::parse($interview->end_at)->format('H:i') }}  
**Mode**: {{ strtoupper($interview->mode) }}  
@isset($interview->meeting_link)
**Meeting Link**: {{ $interview->meeting_link }}
@endisset
@isset($interview->location)
**Lokasi**: {{ $interview->location }}
@endisset

Silakan lihat lampiran **.ics** untuk menambahkan ke kalender Anda.

@component('mail::button', ['url' => $interview->meeting_link ?? url('/')])
Buka Detail
@endcomponent

Terima kasih,  
HR Team
@endcomponent
