@extends('layouts.app', ['title' => 'Admin · Candidate · '.$profile->full_name])

@section('content')
@php
  $BORD='#e5e7eb';

  // Helper kecil buat format tanggal (string/Carbon/null -> tampil rapi)
  $fmtDate = function ($v, $fallback = '—') {
      try {
          if (!$v) return $fallback;
          if ($v instanceof \Illuminate\Support\Carbon || $v instanceof \Carbon\Carbon) {
              return $v->format('d M Y');
          }
          // coba parse string
          $dt = \Illuminate\Support\Carbon::parse($v);
          return $dt->format('d M Y');
      } catch (\Throwable $e) {
          return is_string($v) ? $v : $fallback;
      }
  };

  // Normalisasi dokumen supaya selalu array
  $docsRaw = $profile->getAttribute('documents');
  if (is_string($docsRaw)) {
      $dec = json_decode($docsRaw, true);
      $docs = is_array($dec) ? $dec : [];
  } elseif (is_array($docsRaw)) {
      $docs = $docsRaw;
  } else {
      $docs = [];
  }
@endphp

<div class="space-y-6">

  <div class="rounded-2xl border bg-white shadow-sm">
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 90%"></div>
        <div class="h-full bg-red-500"  style="width: 10%"></div>
      </div>
    </div>

    <div class="p-6 md:p-7">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">{{ $profile->full_name }}</h1>
          <div class="mt-1 text-sm text-slate-600">
            {{ $profile->email }} · {{ $profile->phone }}
          </div>
        </div>
        <div class="flex gap-2">
          @if($profile->cv_path && Route::has('admin.candidates.cv'))
            <a class="btn btn-primary" href="{{ route('admin.candidates.cv', $profile) }}" target="_blank">Lihat CV</a>
          @endif
          @if(Route::has('admin.candidates.index'))
            <a class="btn btn-ghost" href="{{ route('admin.candidates.index') }}">Kembali</a>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="grid gap-6 lg:grid-cols-3">
    {{-- Kiri: Data Pribadi & Alamat --}}
    <div class="lg:col-span-2 space-y-6">
      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h2 class="text-lg font-semibold text-slate-900">Data Pribadi</h2>
        <dl class="mt-3 grid grid-cols-3 gap-y-2 text-sm">
          <dt class="text-slate-500">Nama Lengkap</dt><dd class="col-span-2 text-slate-800">{{ $profile->full_name }}</dd>
          <dt class="text-slate-500">Nama Panggilan</dt><dd class="col-span-2 text-slate-800">{{ $profile->nickname ?: '—' }}</dd>
          <dt class="text-slate-500">Gender</dt><dd class="col-span-2 text-slate-800">{{ $profile->gender ?: '—' }}</dd>
          <dt class="text-slate-500">Usia</dt><dd class="col-span-2 text-slate-800">{{ $profile->age !== null ? $profile->age : '—' }}</dd>
          <dt class="text-slate-500">TTL</dt><dd class="col-span-2 text-slate-800">{{ $profile->birthplace ?: '—' }}, {{ $fmtDate($profile->birthdate) }}</dd>
          <dt class="text-slate-500">NIK</dt><dd class="col-span-2 text-slate-800">{{ $profile->nik ?: '—' }}</dd>
          <dt class="text-slate-500">Email</dt><dd class="col-span-2 text-slate-800">{{ $profile->email ?: '—' }}</dd>
          <dt class="text-slate-500">HP</dt><dd class="col-span-2 text-slate-800">{{ $profile->phone ?: '—' }}</dd>
          <dt class="text-slate-500">WhatsApp</dt><dd class="col-span-2 text-slate-800">{{ $profile->whatsapp ?: '—' }}</dd>
        </dl>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
          <div class="rounded-xl border p-4" style="border-color: {{ $BORD }}">
            <div class="text-xs text-slate-500">Pendidikan Terakhir</div>
            <div class="mt-1 font-semibold">{{ $profile->last_education ?: '—' }}</div>
          </div>
          <div class="rounded-xl border p-4" style="border-color: {{ $BORD }}">
            <div class="text-xs text-slate-500">Jurusan</div>
            <div class="mt-1 font-semibold">{{ $profile->education_major ?: '—' }}</div>
          </div>
          <div class="rounded-xl border p-4" style="border-color: {{ $BORD }}">
            <div class="text-xs text-slate-500">Sekolah/Kampus</div>
            <div class="mt-1 font-semibold">{{ $profile->education_school ?: '—' }}</div>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h2 class="text-lg font-semibold text-slate-900">Alamat KTP</h2>
        <div class="text-sm text-slate-800 whitespace-pre-line">{{ $profile->ktp_address ?: '—' }}</div>
        <div class="mt-2 grid grid-cols-3 gap-y-1 text-sm">
          <div>RT/RW: {{ $profile->ktp_rt ?: '—' }} / {{ $profile->ktp_rw ?: '—' }}</div>
          <div>Kel/Desa: {{ $profile->ktp_village ?: '—' }}</div>
          <div>Kecamatan: {{ $profile->ktp_district ?: '—' }}</div>
          <div>Kota/Kab: {{ $profile->ktp_city ?: '—' }}</div>
          <div>Provinsi: {{ $profile->ktp_province ?: '—' }}</div>
          <div>Kode Pos: {{ $profile->ktp_postal_code ?: '—' }}</div>
          <div>Status Tinggal: {{ $profile->ktp_residence_status ?: '—' }}</div>
        </div>
      </div>

      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h2 class="text-lg font-semibold text-slate-900">Alamat Domisili</h2>
        <div class="text-sm text-slate-800 whitespace-pre-line">{{ $profile->domicile_address ?: '—' }}</div>
        <div class="mt-2 grid grid-cols-3 gap-y-1 text-sm">
          <div>RT/RW: {{ $profile->domicile_rt ?: '—' }} / {{ $profile->domicile_rw ?: '—' }}</div>
          <div>Kel/Desa: {{ $profile->domicile_village ?: '—' }}</div>
          <div>Kecamatan: {{ $profile->domicile_district ?: '—' }}</div>
          <div>Kota/Kab: {{ $profile->domicile_city ?: '—' }}</div>
          <div>Provinsi: {{ $profile->domicile_province ?: '—' }}</div>
          <div>Kode Pos: {{ $profile->domicile_postal_code ?: '—' }}</div>
          <div>Status Tinggal: {{ $profile->domicile_residence_status ?: '—' }}</div>
        </div>
      </div>
    </div>

    {{-- Kanan: Repeater & Dokumen --}}
    <aside class="space-y-6">
      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h3 class="text-base font-semibold text-slate-900">Pelatihan / Sertifikasi</h3>
        <ul class="mt-3 space-y-2 text-sm">
          @forelse($profile->trainings as $t)
            <li class="rounded border p-3" style="border-color: {{ $BORD }}">
              <div class="font-medium">{{ $t->title }}</div>
              <div class="text-slate-600">{{ $t->institution }}</div>
              <div class="text-xs text-slate-500">
                {{ $fmtDate($t->period_start) }} — {{ $t->period_end ? $fmtDate($t->period_end) : 'sekarang/—' }}
              </div>
            </li>
          @empty
            <li class="text-slate-500">—</li>
          @endforelse
        </ul>
      </div>

      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h3 class="text-base font-semibold text-slate-900">Riwayat Pekerjaan</h3>
        <ul class="mt-3 space-y-2 text-sm">
          @forelse($profile->employments as $e)
            <li class="rounded border p-3" style="border-color: {{ $BORD }}">
              <div class="font-medium">{{ $e->company }}</div>
              <div class="text-slate-600">{{ $e->position_start }} → {{ $e->position_end ?: '—' }}</div>
              <div class="text-xs text-slate-500">{{ $fmtDate($e->period_start) }} — {{ $e->period_end ? $fmtDate($e->period_end) : 'sekarang/—' }}</div>
              @if($e->reason_for_leaving)
                <div class="text-xs text-slate-500">Alasan: {{ $e->reason_for_leaving }}</div>
              @endif
              @if($e->job_description)
                <div class="mt-1 whitespace-pre-line">{{ $e->job_description }}</div>
              @endif
            </li>
          @empty
            <li class="text-slate-500">—</li>
          @endforelse
        </ul>
      </div>

      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h3 class="text-base font-semibold text-slate-900">Referensi</h3>
        <ul class="mt-3 space-y-2 text-sm">
          @forelse($profile->references as $r)
            <li class="rounded border p-3" style="border-color: {{ $BORD }}">
              <div class="font-medium">{{ $r->name }}</div>
              <div class="text-slate-600">{{ $r->job_title }} @ {{ $r->company }}</div>
              <div class="text-xs text-slate-500">{{ $r->contact }}</div>
            </li>
          @empty
            <li class="text-slate-500">—</li>
          @endforelse
        </ul>
      </div>

      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h3 class="text-base font-semibold text-slate-900">Dokumen</h3>

        @if($profile->cv_path && Route::has('admin.candidates.cv'))
          <div class="mt-2 text-sm">
            CV: <a class="text-blue-700 hover:underline" target="_blank" href="{{ route('admin.candidates.cv',$profile) }}">Lihat</a>
          </div>
        @endif

        @if(!empty($docs))
          <ul class="mt-2 space-y-1 text-sm list-disc pl-5">
            @foreach($docs as $d)
              @php
                $name = is_array($d) ? ($d['name'] ?? 'Dokumen') : (string) $d;
                $path = is_array($d) ? ($d['path'] ?? null) : null;
              @endphp
              <li>
                @if($path)
                  <a class="text-blue-700 hover:underline" target="_blank" href="{{ Storage::disk('public')->url($path) }}">{{ $name }}</a>
                @else
                  {{ $name }}
                @endif
              </li>
            @endforeach
          </ul>
        @else
          <div class="mt-2 text-sm text-slate-500">Tidak ada dokumen pendukung.</div>
        @endif
      </div>
    </aside>
  </div>

  <div class="text-xs text-slate-500">
    Dibuat: {{ $fmtDate(optional($profile->created_at), '—') }} ·
    Diubah: {{ $fmtDate(optional($profile->updated_at), '—') }}
  </div>
</div>
@endsection
