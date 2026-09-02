{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app', ['title' => 'Profile'])

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
    $profile = $candidateProfile ?? $user->candidateProfile;
    $displayName = $profile?->full_name ?: $user->name;
    $displayEmail = $profile?->email ?: $user->email;
    $initials = collect(explode(' ', trim((string) $displayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('') ?: 'U';

    $fmtDate = function ($value, $fallback = '-') {
        try {
            if (! $value) return $fallback;
            if ($value instanceof \Illuminate\Support\Carbon || $value instanceof \Carbon\Carbon) {
                return $value->format('d M Y');
            }
            return \Illuminate\Support\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    $fmtDateTime = function ($value, $fallback = '-') {
        try {
            if (! $value) return $fallback;
            if ($value instanceof \Illuminate\Support\Carbon || $value instanceof \Carbon\Carbon) {
                return $value->format('d M Y, H:i');
            }
            return \Illuminate\Support\Carbon::parse($value)->format('d M Y, H:i');
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    $fmtMoney = function ($value, $fallback = '-') {
        if ($value === null || $value === '') return $fallback;
        if (! is_numeric($value)) return $value;
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    };

    $fullAddress = function ($prefix) use ($profile) {
        if (! $profile) return '-';

        $parts = [
            $profile->{$prefix . '_address'},
            trim(collect([$profile->{$prefix . '_rt'}, $profile->{$prefix . '_rw'}])->filter()->implode('/')) ? 'RT/RW ' . collect([$profile->{$prefix . '_rt'}, $profile->{$prefix . '_rw'}])->filter()->implode('/') : null,
            $profile->{$prefix . '_village'},
            $profile->{$prefix . '_district'},
            $profile->{$prefix . '_city'},
            $profile->{$prefix . '_province'},
            $profile->{$prefix . '_postal_code'},
        ];

        return collect($parts)->filter(fn ($item) => filled($item))->implode(', ') ?: '-';
    };

    $profileCompleteness = 0;
    if ($profile) {
        $required = [
            'full_name', 'gender', 'birthplace', 'birthdate', 'nik', 'phone', 'email',
            'last_education', 'education_major', 'education_school',
            'ktp_address', 'ktp_city', 'ktp_province',
            'domicile_address', 'domicile_city', 'domicile_province',
            'cv_path',
        ];
        $filledCount = collect($required)->filter(fn ($field) => filled($profile->{$field}))->count();
        $profileCompleteness = (int) round(($filledCount / count($required)) * 100);
    }

    $documents = collect();
    if ($profile?->cv_path) {
        $documents->push(['name' => 'CV', 'path' => $profile->cv_path]);
    }
    foreach ((array) ($profile?->documents ?? []) as $document) {
        if (is_array($document) && filled($document['path'] ?? null)) {
            $documents->push([
                'name' => $document['name'] ?? basename($document['path']),
                'path' => $document['path'],
            ]);
        }
    }
    $profile?->trainings?->each(function ($training) use ($documents) {
        if (filled($training->certificate_path)) {
            $documents->push([
                'name' => $training->certificate_name ?: ('Sertifikat ' . ($training->title ?: 'Pelatihan')),
                'path' => $training->certificate_path,
            ]);
        }
    });
    $documents = $documents->unique('path')->values();

    $profileEditJob = $latestApplication?->job;
    $profileEditUrl = $profileEditJob && Route::has('candidate.profiles.edit')
        ? route('candidate.profiles.edit', $profileEditJob)
        : (Route::has('jobs.index') ? route('jobs.index') : url('/jobs'));
@endphp

@section('content')
<div class="page-container">
  {{-- ===== PROFILE HEADER (solid brown, shared design) ===== --}}
  <section class="page-header" aria-labelledby="profile-name">
    <div class="page-header__inner">
      <div class="flex items-center gap-4 min-w-0">
        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-3xl font-bold text-white ring-1 ring-white/30">
          {{ $initials }}
        </div>
        <div class="page-header__copy min-w-0">
          <p class="page-header__eyebrow">Profil Kandidat</p>
          <h1 id="profile-name" class="page-header__title truncate" style="max-width:100%">{{ $displayName }}</h1>
          <p class="page-header__desc mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
            <span class="inline-flex items-center gap-1.5">
              <svg class="w-4 h-4 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
              {{ $displayEmail }}
            </span>
            <span class="inline-flex items-center gap-1.5">
              <svg class="w-4 h-4 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.6 2.8a2 2 0 0 1-.4 2.1L8 10a16 16 0 0 0 6 6l1.4-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.5 2.8.6a2 2 0 0 1 1.7 2Z"/></svg>
              {{ $profile?->phone ?: 'Nomor HP belum diisi' }}
            </span>
            @if($user->email_verified_at)
              <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-1 text-xs font-semibold text-white ring-1 ring-white/30">Verified</span>
            @else
              <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-1 text-xs font-semibold text-white ring-1 ring-white/30">Belum Verifikasi</span>
            @endif
          </p>
        </div>
      </div>

      <div class="page-header__actions pt-2">
        <a href="{{ $profileEditUrl }}" class="ph-action">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
          Perbarui Biodata
        </a>
        @if(Route::has('applications.mine'))
          <a href="{{ route('applications.mine') }}" class="ph-action">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1"/></svg>
            Lihat Lamaran
          </a>
        @endif
      </div>
    </div>
  </section>

  {{-- ===== STAT CARDS ===== --}}
  <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan profil">
    <div class="rounded-2xl border border-[#ede4dc] bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kelengkapan</div>
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-[#fdf7f0] text-[#a77d52]">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
        </span>
      </div>
      <div class="mt-2 text-3xl font-bold text-[#5c3d1e]">{{ $profileCompleteness }}%</div>
      <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-[#f0e7dc]">
        <div class="h-2.5 rounded-full bg-[#a77d52]" style="width: {{ $profileCompleteness }}%"></div>
      </div>
    </div>
    <div class="rounded-2xl border border-[#ede4dc] bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lamaran</div>
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-[#fdf7f0] text-[#a77d52]">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1"/></svg>
        </span>
      </div>
      <div class="mt-2 text-3xl font-bold text-[#5c3d1e]">{{ $user->applications->count() }}</div>
      <div class="mt-1 text-sm text-slate-500">Total posisi yang dilamar</div>
    </div>
    <div class="rounded-2xl border border-[#ede4dc] bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pelatihan</div>
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-[#fdf7f0] text-[#a77d52]">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 10 5-10 5L2 8Z"/><path d="M6 10v5c0 1 3 3 6 3s6-2 6-3v-5"/></svg>
        </span>
      </div>
      <div class="mt-2 text-3xl font-bold text-[#5c3d1e]">{{ $profile?->trainings?->count() ?? 0 }}</div>
      <div class="mt-1 text-sm text-slate-500">Data sertifikasi</div>
    </div>
    <div class="rounded-2xl border border-[#ede4dc] bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pengalaman</div>
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-[#fdf7f0] text-[#a77d52]">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14M9 9h6M9 13h6M9 17h4"/></svg>
        </span>
      </div>
      <div class="mt-2 text-3xl font-bold text-[#5c3d1e]">{{ $profile?->employments?->count() ?? 0 }}</div>
      <div class="mt-1 text-sm text-slate-500">Riwayat pekerjaan</div>
    </div>
  </section>

  @if(! $profile)
    <section class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
      <h2 class="text-lg font-semibold">Biodata kandidat belum dibuat</h2>
      <p class="mt-1 text-sm">Lengkapi biodata saat melamar lowongan supaya data lengkap kamu muncul di halaman ini.</p>
      <a href="{{ Route::has('jobs.index') ? route('jobs.index') : url('/jobs') }}" class="abtn abtn-primary mt-4">Cari Lowongan</a>
    </section>
  @else
    <div class="mt-6 grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
      <aside class="space-y-6">
        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <x-section-title title="Intro" />
          <dl class="mt-4 space-y-3 text-sm">
            <div>
              <dt class="text-xs font-medium uppercase text-slate-500">Nama panggilan</dt>
              <dd class="mt-1 font-semibold text-slate-900">{{ $profile->nickname ?: '-' }}</dd>
            </div>
            <div>
              <dt class="text-xs font-medium uppercase text-slate-500">NIK KTP</dt>
              <dd class="mt-1 font-semibold text-slate-900">{{ $profile->nik ?: '-' }}</dd>
            </div>
            <div>
              <dt class="text-xs font-medium uppercase text-slate-500">Tempat, tanggal lahir</dt>
              <dd class="mt-1 font-semibold text-slate-900">{{ $profile->birthplace ?: '-' }}, {{ $fmtDate($profile->birthdate) }}</dd>
            </div>
            <div>
              <dt class="text-xs font-medium uppercase text-slate-500">POH</dt>
              <dd class="mt-1 font-semibold text-slate-900">{{ $profile->poh?->name ?: '-' }}</dd>
            </div>
          </dl>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <x-section-title title="Kontak" />
          <div class="mt-4 space-y-3 text-sm">
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-3">
              <div class="text-xs font-medium text-slate-500">Email</div>
              <div class="mt-1 break-all font-semibold text-slate-900">{{ $displayEmail ?: '-' }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-3">
              <div class="text-xs font-medium text-slate-500">HP</div>
              <div class="mt-1 font-semibold text-slate-900">{{ $profile->phone ?: '-' }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-3">
              <div class="text-xs font-medium text-slate-500">WhatsApp</div>
              <div class="mt-1 font-semibold text-slate-900">{{ $profile->whatsapp ?: '-' }}</div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <x-section-title title="Dokumen" :badge="$documents->count()" />
          <div class="mt-4 space-y-2">
            @forelse($documents as $document)
              <a target="_blank" href="{{ Storage::disk('public')->url($document['path']) }}" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                <span class="min-w-0 truncate font-medium text-slate-800">{{ $document['name'] }}</span>
                <span class="shrink-0 text-xs font-semibold text-[#8b5e3c]">Lihat</span>
              </a>
            @empty
              <div class="rounded-xl bg-slate-50 p-3 text-sm text-slate-500">Belum ada dokumen tersimpan.</div>
            @endforelse
          </div>
        </section>
      </aside>

      <main class="space-y-6">
        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <x-section-title title="Tentang Kandidat" />
          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Gender</div>
              <div class="mt-1 font-semibold capitalize text-slate-950">{{ $profile->gender ?: '-' }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Usia</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->age ? $profile->age . ' tahun' : '-' }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Status pernikahan</div>
              <div class="mt-1 font-semibold capitalize text-slate-950">{{ $profile->status_pernikahan ?: '-' }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Sumber informasi</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->source_channel ? Str::headline($profile->source_channel) : '-' }}</div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <x-section-title title="Alamat" />
          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-slate-200 p-4">
              <div class="text-xs font-semibold uppercase text-slate-500">KTP</div>
              <p class="mt-2 text-sm leading-6 text-slate-800">{{ $fullAddress('ktp') }}</p>
              <div class="mt-3 text-xs text-slate-500">Status tempat tinggal: <span class="font-semibold text-slate-700">{{ $profile->ktp_residence_status ?: '-' }}</span></div>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
              <div class="text-xs font-semibold uppercase text-slate-500">Domisili</div>
              <p class="mt-2 text-sm leading-6 text-slate-800">{{ $fullAddress('domicile') }}</p>
              <div class="mt-3 text-xs text-slate-500">Status tempat tinggal: <span class="font-semibold text-slate-700">{{ $profile->domicile_residence_status ?: '-' }}</span></div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <x-section-title title="Pendidikan & Kesiapan Kerja" icon='<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 9-10-5L2 9l10 5 10-5Z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/></svg>' />
          <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Pendidikan</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->last_education ?: '-' }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Jurusan</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->education_major ?: '-' }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Sekolah / Kampus</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->education_school ?: '-' }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Siap mulai</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $fmtDate($profile->available_start_date) }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Gaji saat ini</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $fmtMoney($profile->current_salary) }}</div>
            </div>
            <div class="rounded-xl border border-[#f0e7dc] bg-[#fdf7f0] p-4">
              <div class="text-xs font-medium text-slate-500">Ekspektasi gaji</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $fmtMoney($profile->expected_salary) }}</div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <x-section-title title="Riwayat Lamaran" icon='<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1"/></svg>' />
          <div class="mt-4 space-y-3">
            @forelse($user->applications->sortByDesc('created_at') as $application)
              <div class="rounded-xl border border-slate-200 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <div class="font-semibold text-slate-950">{{ $application->job?->title ?: 'Lowongan' }}</div>
                    <div class="mt-1 text-sm text-slate-500">{{ $application->job?->code ?: '-' }} · {{ $application->job?->site?->name ?: 'Site belum diisi' }}</div>
                  </div>
                  <span class="rounded-full bg-[#fff4e4] px-3 py-1 text-xs font-semibold capitalize text-[#8b5e3c]">{{ str_replace('_', ' ', $application->current_stage ?: $application->overall_status ?: 'active') }}</span>
                </div>
                <div class="mt-3 text-xs text-slate-500">Diajukan: {{ $fmtDateTime($application->created_at) }}</div>
              </div>
            @empty
              <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada lamaran tersimpan.</div>
            @endforelse
          </div>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <x-section-title title="Pengalaman Kerja" icon='<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14M9 9h6M9 13h6M9 17h4"/></svg>' />
          <div class="mt-4 space-y-3">
            @forelse($profile->employments as $employment)
              <div class="rounded-xl border border-slate-200 p-4">
                <div class="font-semibold text-slate-950">{{ $employment->company ?: '-' }}</div>
                <div class="mt-1 text-sm text-slate-600">{{ $employment->position_start ?: '-' }}{{ $employment->position_end ? ' ke ' . $employment->position_end : '' }}</div>
                <div class="mt-2 text-xs text-slate-500">{{ $fmtDate($employment->period_start) }} - {{ $fmtDate($employment->period_end) }}</div>
                @if(filled($employment->reason_for_leaving))
                  <p class="mt-3 text-sm text-slate-700">Alasan keluar: {{ $employment->reason_for_leaving }}</p>
                @endif
              </div>
            @empty
              <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada riwayat pekerjaan.</div>
            @endforelse
          </div>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <x-section-title title="Pelatihan & Referensi" icon='<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z"/><path d="M20 17v3a1 1 0 0 1-1 1H6.5a2.5 2.5 0 0 1 0-5"/></svg>' />
          <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div class="space-y-3">
              @forelse($profile->trainings as $training)
                <div class="rounded-xl border border-slate-200 p-4">
                  <div class="font-semibold text-slate-950">{{ $training->title ?: '-' }}</div>
                  <div class="mt-1 text-sm text-slate-600">{{ $training->institution ?: '-' }}</div>
                  <div class="mt-2 text-xs text-slate-500">{{ $fmtDate($training->period_start) }} - {{ $fmtDate($training->period_end) }}</div>
                </div>
              @empty
                <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada pelatihan.</div>
              @endforelse
            </div>
            <div class="space-y-3">
              @forelse($profile->references as $reference)
                <div class="rounded-xl border border-slate-200 p-4">
                  <div class="font-semibold text-slate-950">{{ $reference->name ?: '-' }}</div>
                  <div class="mt-1 text-sm text-slate-600">{{ $reference->job_title ?: '-' }}{{ $reference->company ? ' · ' . $reference->company : '' }}</div>
                  <div class="mt-2 text-xs text-slate-500">{{ $reference->contact ?: '-' }}</div>
                </div>
              @empty
                <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada referensi.</div>
              @endforelse
            </div>
          </div>
        </section>
      </main>
    </div>
  @endif

  <div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
      <x-section-title title="Informasi Akun" icon='<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>' />
      <div class="mt-4 max-w-xl">
        @include('profile.partials.update-profile-information-form')
      </div>
    </section>

    <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
      <x-section-title title="Keamanan" icon='<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>' />
      <div class="mt-4 max-w-xl">
        @include('profile.partials.update-password-form')
      </div>
    </section>
  </div>

  <section class="mt-6 rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
    <x-section-title title="Hapus Akun" icon='<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>' />
    <div class="mt-4 max-w-xl">
      @include('profile.partials.delete-user-form')
    </div>
  </section>
</div>
@endsection
