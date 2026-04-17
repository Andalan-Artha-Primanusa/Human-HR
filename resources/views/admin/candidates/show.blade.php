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

<div class="flex flex-col gap-6 lg:flex-row">
  {{-- SIDEBAR KANAN --}}
  <aside class="flex flex-col order-2 w-full gap-4 lg:w-1/3 lg:order-1">
    <div class="flex flex-col items-center p-4 bg-white border shadow-sm rounded-2xl">
      <div class="w-16 h-16 rounded-full bg-[#a77d52] flex items-center justify-center text-white text-2xl font-bold mb-2">
        {{ Str::substr($profile->full_name, 0, 1) }}
      </div>
      <div class="text-center">
        <div class="font-semibold">{{ $profile->full_name }}</div>
        <div class="text-xs text-slate-500">{{ $profile->email }}<br>{{ $profile->phone }}</div>
      </div>
      <div class="flex gap-2 mt-3">
        @if($profile->cv_path)
          <a target="_blank" href="{{ route('admin.candidates.cv',$profile) }}"
             class="inline-flex items-center justify-center gap-2 px-3 py-1 text-xs font-semibold text-white rounded bg-[#a77d52] hover:brightness-105">Lihat CV</a>
        @endif
        <a href="{{ route('admin.candidates.index') }}"
           class="inline-flex items-center px-3 py-1 text-xs bg-white border rounded text-slate-900 border-slate-200 hover:bg-slate-50">Kembali</a>
      </div>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Ringkasan</div>
      <div class="grid grid-cols-3 gap-2 text-center">
        <div>
          <div class="text-xl font-bold">{{ $profile->trainings->count() ?? 0 }}</div>
          <div class="text-xs text-slate-500">Pelatihan</div>
        </div>
        <div>
          <div class="text-xl font-bold">{{ $profile->employments->count() ?? 0 }}</div>
          <div class="text-xs text-slate-500">Pekerjaan</div>
        </div>
        <div>
          <div class="text-xl font-bold">{{ $profile->references->count() ?? 0 }}</div>
          <div class="text-xs text-slate-500">Referensi</div>
        </div>
      </div>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Dokumen</div>
      @forelse($docs as $d)
        <div class="flex items-center justify-between mb-1">
          <span class="text-sm truncate">{{ $d['name'] ?? 'Dokumen' }}</span>
          <a target="_blank" href="{{ Storage::disk('public')->url($d['path'] ?? '') }}" class="ml-2 px-2 py-1 text-xs rounded bg-[#a77d52] text-white">Lihat</a>
        </div>
      @empty <div class="text-xs text-slate-400">—</div> @endforelse
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Status Profil</div>
      <div class="flex justify-between mb-1 text-xs"><span>Dibuat</span><span>{{ $fmtDate($profile->created_at) }}</span></div>
      <div class="flex justify-between mb-1 text-xs"><span>Diubah</span><span>{{ $fmtDate($profile->updated_at) }}</span></div>
      <div class="mt-2">
        <div class="w-full h-3 mb-2 rounded-full bg-slate-100">
          <div class="bg-[#a77d52] h-3 rounded-full" style="width: 80%"></div>
        </div>
        <div class="text-xs text-center">Kelengkapan Profil<br><span class="font-semibold">80% Lengkap</span></div>
      </div>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Kesiapan Kerja</div>
      <div class="flex justify-between mb-1 text-xs"><span>Bersedia Luar Kota</span><span>{{ $profile->willing_out_of_town ? 'Ya' : 'Tidak' }}</span></div>
      <div class="flex justify-between mb-1 text-xs"><span>Siap Mulai</span><span>{{ $fmtDate($profile->available_start_date) }}</span></div>
      <div class="flex justify-between mb-1 text-xs"><span>Ekspektasi Gaji</span><span>{{ $fmtRupiah($profile->expected_salary) }}</span></div>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Motivasi & Pernyataan</div>
      <div class="text-xs whitespace-pre-line">{{ $profile->work_motivation ?: '—' }}</div>
    </div>
  </aside>

  {{-- MAIN CONTENT KIRI --}}
  <div class="flex flex-col flex-1 order-1 gap-4 lg:order-2">
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Data Pribadi</div>
      <div class="grid grid-cols-2 text-sm gap-x-4 gap-y-1">
        <div>Nama Lengkap</div><div class="font-semibold">{{ $profile->full_name }}</div>
        <div>Nama Panggilan</div><div>{{ $profile->nickname ?: '—' }}</div>
        <div>Gender</div><div>{{ $profile->gender ?: '—' }}</div>
        <div>Usia</div><div>{{ $profile->age ?? '—' }}</div>
        <div>TTL</div><div>{{ $profile->birthplace }}, {{ $fmtDate($profile->birthdate) }}</div>
        <div>NIK</div><div>{{ $profile->nik ?: '—' }}</div>
        <div>Email</div><div>{{ $profile->email ?: '—' }}</div>
        <div>HP</div><div>{{ $profile->phone ?: '—' }}</div>
        <div>WhatsApp</div><div>{{ $profile->whatsapp ?: '—' }}</div>
      </div>
      <div class="flex gap-2 mt-3">
        <div class="px-3 py-1 text-xs rounded bg-slate-100">Pendidikan Terakhir<br><span class="font-semibold">{{ $profile->last_education ?: '—' }}</span></div>
        <div class="px-3 py-1 text-xs rounded bg-slate-100">Jurusan<br><span class="font-semibold">{{ $profile->education_major ?: '—' }}</span></div>
        <div class="px-3 py-1 text-xs rounded bg-slate-100">Sekolah / Kampus<br><span class="font-semibold">{{ $profile->education_school ?: '—' }}</span></div>
      </div>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Alamat KTP</div>
      <div class="text-sm">{{ $profile->ktp_address ?: '—' }}</div>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Alamat Domisili</div>
      <div class="text-sm">{{ $profile->domicile_address ?: '—' }}</div>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Pelatihan & Sertifikasi</div>
      <table class="w-full text-xs border-t">
        <thead>
          <tr class="text-left text-slate-500">
            <th class="py-1">Judul Pelatihan</th>
            <th class="py-1">Institusi</th>
            <th class="py-1">Periode</th>
          </tr>
        </thead>
        <tbody>
          @forelse($profile->trainings as $t)
            <tr>
              <td class="py-1">{{ $t->title }}</td>
              <td class="py-1">{{ $t->institution }}</td>
              <td class="py-1">{{ $fmtDate($t->period_start) }}{{ $t->period_end ? ' – '.$fmtDate($t->period_end) : '' }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-slate-400">—</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Riwayat Pekerjaan</div>
      <table class="w-full text-xs border-t">
        <thead>
          <tr class="text-left text-slate-500">
            <th class="py-1">Perusahaan</th>
            <th class="py-1">Jabatan Awal / Akhir</th>
            <th class="py-1">Periode</th>
            <th class="py-1">Alasan Keluar</th>
          </tr>
        </thead>
        <tbody>
          @forelse($profile->employments as $e)
            <tr>
              <td class="py-1">{{ $e->company }}</td>
              <td class="py-1">{{ $e->position_start }}<br><span class="text-slate-400">{{ $e->position_end ?: '—' }}</span></td>
              <td class="py-1">{{ $fmtDate($e->period_start) }}<br><span class="text-slate-400">{{ $fmtDate($e->period_end) }}</span></td>
              <td class="py-1">{{ $e->reason_for_leaving ?: '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-slate-400">—</td></tr>
          @endforelse
        </tbody>
      </table>
      @if($profile->employments->first()?->job_description)
        <div class="mt-2 text-xs text-slate-600"><strong>Deskripsi Pekerjaan Terakhir:</strong><br>{{ $profile->employments->first()->job_description }}</div>
      @endif
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Referensi</div>
      <table class="w-full text-xs border-t">
        <thead>
          <tr class="text-left text-slate-500">
            <th class="py-1">Nama</th>
            <th class="py-1">Jabatan</th>
            <th class="py-1">Perusahaan</th>
            <th class="py-1">Kontak</th>
          </tr>
        </thead>
        <tbody>
          @forelse($profile->references as $r)
            <tr>
              <td class="py-1">{{ $r->name }}</td>
              <td class="py-1">{{ $r->job_title }}</td>
              <td class="py-1">{{ $r->company }}</td>
              <td class="py-1">{{ $r->contact }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-slate-400">—</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Gaji & Kesiapan Kerja</div>
      <div class="grid grid-cols-2 text-sm gap-x-4 gap-y-1">
        <div>Gaji Saat Ini</div><div>{{ $fmtRupiah($profile->current_salary) }}</div>
        <div>Gaji Diharapkan</div><div>{{ $fmtRupiah($profile->expected_salary) }}</div>
        <div>Siap Mulai</div><div>{{ $fmtDate($profile->available_start_date) }}</div>
      </div>
      <div class="mt-2 text-xs"><strong>Fasilitas Diharapkan:</strong> {{ $profile->expected_facilities ?: '—' }}</div>
      <div class="mt-2 text-xs"><strong>Motivasi Kerja:</strong> {{ $profile->work_motivation ?: '—' }}</div>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Informasi Tambahan</div>
      <div class="grid grid-cols-2 text-sm gap-x-4 gap-y-1">
        <div>Status Pernikahan</div><div>{{ $profile->marital_status ?? '—' }}</div>
        <div>Kerabat di Perusahaan?</div><div>{{ $profile->has_relatives ? 'Ya' : 'Tidak' }}</div>
        <div>Detail Kerabat</div><div>{{ $profile->relatives_detail ?: '—' }}</div>
        <div>Pernah Bekerja di Sini?</div><div>{{ $profile->worked_before ? 'Ya' : 'Tidak' }}</div>
        <div>Posisi Sebelumnya</div><div>{{ $profile->worked_before_position ?: '—' }}</div>
        <div>Durasi Bekerja</div><div>{{ $profile->worked_before_duration ?: '—' }}</div>
        <div>Pernah Melamar?</div><div>{{ $profile->applied_before ? 'Ya' : 'Tidak' }}</div>
        <div>Posisi Dilamar Sebelumnya</div><div>{{ $profile->applied_before_position ?: '—' }}</div>
        <div>Bersedia Luar Kota?</div><div>{{ $profile->willing_out_of_town ? 'Ya' : 'Tidak' }}</div>
        <div>Alasan Tidak Bersedia</div><div>{{ $profile->not_willing_reason ?: '—' }}</div>
        <div>Extras</div><div>—</div>
      </div>
    </div>
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="mb-2 font-semibold">Kesehatan</div>
      <div class="grid grid-cols-2 text-sm gap-x-4 gap-y-1">
        <div>Medical History</div><div>{{ $profile->medical_history ?: 'Tidak ada riwayat penyakit kronis' }}</div>
        <div>Pemeriksaan Terakhir</div><div>{{ $profile->last_medical_checkup ?: '—' }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
