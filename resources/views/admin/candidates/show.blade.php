@extends('layouts.app', ['title' => 'Admin · Candidate · '.$profile->full_name])

@section('content')
@php
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
<div class="rounded-2xl border bg-white shadow-sm">
  <div class="h-2 flex rounded-t-2xl overflow-hidden">
    <div class="bg-blue-600 w-[90%]"></div>
    <div class="bg-red-500 w-[10%]"></div>
  </div>

  <div class="p-6 md:p-7 flex justify-between items-start">
    <div>
      <h1 class="text-2xl md:text-3xl font-semibold text-slate-900">{{ $profile->full_name }}</h1>
      <div class="mt-1 text-sm text-slate-600">
        {{ $profile->email }} · {{ $profile->phone }}
      </div>
    </div>
    <div class="flex gap-2">
      @if($profile->cv_path)
        <a class="btn btn-primary" target="_blank" href="{{ route('admin.candidates.cv',$profile) }}">Lihat CV</a>
      @endif
      <a class="btn btn-ghost" href="{{ route('admin.candidates.index') }}">Kembali</a>
    </div>
  </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">

{{-- ===================== KIRI ===================== --}}
<div class="lg:col-span-2 space-y-6">

{{-- DATA PRIBADI --}}
<div class="rounded-2xl border bg-white shadow-sm p-6" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Data Pribadi</h2>
<dl class="mt-3 grid grid-cols-3 gap-y-2 text-sm">
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

<div class="mt-4 grid gap-4 sm:grid-cols-3">
  <div class="rounded-xl border p-4" style="border-color:{{ $BORD }}">
    <div class="text-xs text-slate-500">Pendidikan Terakhir</div>
    <div class="mt-1 font-semibold">{{ $profile->last_education ?: '—' }}</div>
  </div>
  <div class="rounded-xl border p-4" style="border-color:{{ $BORD }}">
    <div class="text-xs text-slate-500">Jurusan</div>
    <div class="mt-1 font-semibold">{{ $profile->education_major ?: '—' }}</div>
  </div>
  <div class="rounded-xl border p-4" style="border-color:{{ $BORD }}">
    <div class="text-xs text-slate-500">Sekolah / Kampus</div>
    <div class="mt-1 font-semibold">{{ $profile->education_school ?: '—' }}</div>
  </div>
</div>
</div>

{{-- ALAMAT KTP --}}
<div class="rounded-2xl border bg-white shadow-sm p-6" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Alamat KTP</h2>
<div class="text-sm whitespace-pre-line">{{ $profile->ktp_address ?: '—' }}</div>
</div>

{{-- ALAMAT DOMISILI --}}
<div class="rounded-2xl border bg-white shadow-sm p-6" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Alamat Domisili</h2>
<div class="text-sm whitespace-pre-line">{{ $profile->domicile_address ?: '—' }}</div>
</div>

{{-- GAJI & KESIAPAN --}}
<div class="rounded-2xl border bg-white shadow-sm p-6" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Gaji & Kesiapan Kerja</h2>
<dl class="mt-3 grid grid-cols-3 gap-y-2 text-sm">
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
<div class="rounded-2xl border bg-white shadow-sm p-6" style="border-color:{{ $BORD }}">
<h2 class="text-lg font-semibold">Kesehatan</h2>
<div class="text-sm">
<strong>Pemeriksaan Terakhir:</strong>
{{ $profile->last_medical_checkup ?: '—' }}
</div>
<div class="mt-2 text-sm whitespace-pre-line">
<strong>Riwayat Penyakit:</strong><br>
{{ $profile->medical_history ?: '—' }}
</div>
</div>

</div>

{{-- ===================== KANAN ===================== --}}
<aside class="space-y-6">

{{-- TRAINING --}}
<div class="rounded-2xl border bg-white shadow-sm p-6">
<h3 class="font-semibold">Pelatihan / Sertifikasi</h3>
@forelse($profile->trainings as $t)
  <div class="mt-2 text-sm">
    <strong>{{ $t->title }}</strong><br>
    {{ $t->institution }}<br>
    <span class="text-xs">{{ $fmtDate($t->period_start) }} — {{ $fmtDate($t->period_end) }}</span>
  </div>
@empty — @endforelse
</div>

{{-- PEKERJAAN --}}
<div class="rounded-2xl border bg-white shadow-sm p-6">
<h3 class="font-semibold">Riwayat Pekerjaan</h3>
@forelse($profile->employments as $e)
  <div class="mt-2 text-sm">
    <strong>{{ $e->company }}</strong><br>
    {{ $e->position_start }} → {{ $e->position_end ?: '—' }}
  </div>
@empty — @endforelse
</div>

{{-- REFERENSI --}}
<div class="rounded-2xl border bg-white shadow-sm p-6">
<h3 class="font-semibold">Referensi</h3>
@forelse($profile->references as $r)
  <div class="mt-2 text-sm">
    <strong>{{ $r->name }}</strong><br>
    {{ $r->job_title }} @ {{ $r->company }}
  </div>
@empty — @endforelse
</div>

{{-- DOKUMEN --}}
<div class="rounded-2xl border bg-white shadow-sm p-6">
<h3 class="font-semibold">Dokumen</h3>
@forelse($docs as $d)
  <a target="_blank" class="block text-sm text-blue-700 underline"
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
