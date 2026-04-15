@extends('layouts.app', ['title' => 'Admin · Candidate · '.$profile->full_name])

@section('content')
@php
  $ACCENT = '#a77d52';
  $ACCENT_DARK = '#8b5e3c';
  $BORD = '#e5e7eb';

  // Format tanggal
  $fmtDate = function ($v, $fallback = '—') {
      try {
          if (!$v) return $fallback;
          if ($v instanceof \Illuminate\Support\Carbon || $v instanceof \Carbon\Carbon) {
              return $v->format('d M Y');
          }
          return \Illuminate\Support\Carbon::parse($v)->format('d M Y');
      } catch (\Throwable $e) {
          return $fallback;
      }
  };

  // FORMAT RUPIAH (WAJIB)
  $fmtRupiah = function ($v, $fallback = '—') {
      if ($v === null || $v === '') return $fallback;
      if (!is_numeric($v)) return $v;
      return 'Rp ' . number_format((float)$v, 0, ',', '.');
  };

  // Dokumen
  $docsRaw = $profile->documents;
  if (is_string($docsRaw)) {
      $docs = json_decode($docsRaw, true) ?: [];
  } elseif (is_array($docsRaw)) {
      $docs = $docsRaw;
  } else {
      $docs = [];
  }
@endphp

<div class="space-y-6">

{{-- HEADER --}}
<section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
  <div class="relative h-20 overflow-hidden sm:h-24 rounded-t-2xl">
    <div class="absolute inset-0" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
    <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

    <div class="relative flex items-center h-full px-5 md:px-6">
      <div class="min-w-0">
        <h1 class="text-2xl font-semibold tracking-tight text-white md:text-3xl">{{ $profile->full_name }}</h1>
        <p class="text-sm text-white/90">{{ $profile->email }} · {{ $profile->phone }}</p>
      </div>
    </div>
  </div>

  <div class="p-6 border-t md:p-7 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]" style="border-color: {{ $BORD }}">
    <div class="flex flex-wrap items-center justify-end gap-2">
      @if($profile->cv_path)
        <a target="_blank" href="{{ route('admin.candidates.cv',$profile) }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-lg bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] hover:brightness-105 focus:outline-none focus:ring-2"
           style="--tw-ring-color: {{ $ACCENT }}">Lihat CV</a>
      @endif
      <a href="{{ route('admin.candidates.index') }}"
         class="inline-flex items-center px-4 py-2 text-sm bg-white border rounded-lg border-slate-200 text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2"
         style="--tw-ring-color: {{ $ACCENT }}">Kembali</a>
    </div>
  </div>
</section>

<div class="grid gap-6 lg:grid-cols-3">

{{-- ===================== KIRI ===================== --}}
<div class="space-y-6 lg:col-span-2">

{{-- DATA PRIBADI --}}
<div class="p-6 bg-white border shadow-sm rounded-2xl" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Data Pribadi</h2>
<dl class="grid grid-cols-3 mt-3 text-sm gap-y-2">
  <dt>Nama Lengkap</dt><dd class="col-span-2">{{ $profile->full_name }}</dd>
  <dt>Nama Panggilan</dt><dd class="col-span-2">{{ $profile->nickname ?: '—' }}</dd>
  <dt>Gender</dt><dd class="col-span-2">{{ $profile->gender ?: '—' }}</dd>
  <dt>Usia</dt><dd class="col-span-2">{{ $profile->age ?? '—' }}</dd>
  <dt>TTL</dt><dd class="col-span-2">{{ $profile->birthplace }}, {{ $fmtDate($profile->birthdate) }}</dd>
  <dt>NIK</dt><dd class="col-span-2">{{ $profile->nik ?: '—' }}</dd>
  <dt>Email</dt><dd class="col-span-2">{{ $profile->email ?: '—' }}</dd>
  <dt>HP</dt><dd class="col-span-2">{{ $profile->phone ?: '—' }}</dd>
  <dt>WhatsApp</dt><dd class="col-span-2">{{ $profile->whatsapp ?: '—' }}</dd>
</dl>

<div class="grid gap-4 mt-4 sm:grid-cols-3">
  <div class="p-4 border rounded-xl" style="border-color:{{ $BORD }}">
    <div class="text-xs text-slate-500">Pendidikan Terakhir</div>
    <div class="mt-1 font-semibold">{{ $profile->last_education ?: '—' }}</div>
  </div>
  <div class="p-4 border rounded-xl" style="border-color:{{ $BORD }}">
    <div class="text-xs text-slate-500">Jurusan</div>
    <div class="mt-1 font-semibold">{{ $profile->education_major ?: '—' }}</div>
  </div>
  <div class="p-4 border rounded-xl" style="border-color:{{ $BORD }}">
    <div class="text-xs text-slate-500">Sekolah / Kampus</div>
    <div class="mt-1 font-semibold">{{ $profile->education_school ?: '—' }}</div>
  </div>
</div>
</div>

{{-- ALAMAT KTP --}}
<div class="p-6 bg-white border shadow-sm rounded-2xl" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Alamat KTP</h2>
<div class="text-sm whitespace-pre-line">{{ $profile->ktp_address ?: '—' }}</div>
</div>

{{-- ALAMAT DOMISILI --}}
<div class="p-6 bg-white border shadow-sm rounded-2xl" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Alamat Domisili</h2>
<div class="text-sm whitespace-pre-line">{{ $profile->domicile_address ?: '—' }}</div>
</div>

{{-- GAJI & KESIAPAN --}}
<div class="p-6 bg-white border shadow-sm rounded-2xl" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Gaji & Kesiapan Kerja</h2>
<dl class="grid grid-cols-3 mt-3 text-sm gap-y-2">
  <dt>Gaji Saat Ini</dt><dd class="col-span-2">{{ $fmtRupiah($profile->current_salary) }}</dd>
  <dt>Gaji Diharapkan</dt><dd class="col-span-2">{{ $fmtRupiah($profile->expected_salary) }}</dd>
  <dt>Siap Mulai</dt><dd class="col-span-2">{{ $fmtDate($profile->available_start_date) }}</dd>
</dl>

<div class="mt-3 text-sm whitespace-pre-line">
<strong>Fasilitas Diharapkan:</strong><br>
{{ $profile->expected_facilities ?: '—' }}
</div>

<div class="mt-3 text-sm whitespace-pre-line">
<strong>Motivasi Kerja:</strong><br>
{{ $profile->work_motivation ?: '—' }}
</div>
</div>

{{-- KESEHATAN --}}
<div class="p-6 bg-white border shadow-sm rounded-2xl" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Kesehatan</h2>
<div class="text-sm">
<strong>Pemeriksaan Terakhir:</strong>
{{ $profile->last_medical_checkup ?: '—' }}
<section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
  <div class="relative h-20 overflow-hidden sm:h-24 rounded-t-2xl">
    <div class="absolute inset-0" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
    <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

    <div class="relative flex items-center h-full px-5 md:px-6">
      <div class="min-w-0">
        <h1 class="text-2xl font-semibold tracking-tight text-white md:text-3xl">{{ $profile->full_name }}</h1>
        <p class="text-sm text-white/90">{{ $profile->email }} · {{ $profile->phone }}</p>
      </div>
    </div>
  </div>

  <div class="p-6 border-t md:p-7 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]" style="border-color: {{ $BORD }}">
    <div class="flex flex-wrap items-center justify-end gap-2">
      @if($profile->cv_path)
        <a target="_blank" href="{{ route('admin.candidates.cv',$profile) }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-lg bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] hover:brightness-105 focus:outline-none focus:ring-2"
           style="--tw-ring-color: {{ $ACCENT }}">Lihat CV</a>
      @endif
      <a href="{{ route('admin.candidates.index') }}"
         class="inline-flex items-center px-4 py-2 text-sm bg-white border rounded-lg border-slate-200 text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2"
         style="--tw-ring-color: {{ $ACCENT }}">Kembali</a>
    </div>
  </div>
</section>
@empty — @endforelse
</div>

{{-- PEKERJAAN --}}
<div class="p-6 bg-white border shadow-sm rounded-2xl">
<h3 class="font-semibold">Riwayat Pekerjaan</h3>
@forelse($profile->employments as $e)
  <div class="mt-2 text-sm">
    <strong>{{ $e->company }}</strong><br>
    {{ $e->position_start }} → {{ $e->position_end ?: '—' }}
  </div>
@empty — @endforelse
</div>

{{-- REFERENSI --}}
<div class="p-6 bg-white border shadow-sm rounded-2xl">
<h3 class="font-semibold">Referensi</h3>
@forelse($profile->references as $r)
  <div class="mt-2 text-sm">
    <strong>{{ $r->name }}</strong><br>
    {{ $r->job_title }} @ {{ $r->company }}
  </div>
@empty — @endforelse
</div>

{{-- DOKUMEN --}}
<div class="p-6 bg-white border shadow-sm rounded-2xl">
<h3 class="font-semibold">Dokumen</h3>
@forelse($docs as $d)
    <a target="_blank" class="block text-sm text-[#8b5e3c] underline"
     href="{{ Storage::disk('public')->url($d['path'] ?? '') }}">
     {{ $d['name'] ?? 'Dokumen' }}
  </a>
@empty — @endforelse
</div>

</aside>
</div>

<div class="text-xs text-slate-500">
Dibuat: {{ $fmtDate($profile->created_at) }} ·
Diubah: {{ $fmtDate($profile->updated_at) }}
</div>

</div>
@endsection
