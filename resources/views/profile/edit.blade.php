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
<div class="mx-auto w-full max-w-[1180px] px-4 py-6 sm:px-6 lg:px-8">
  <section class="overflow-hidden rounded-[18px] border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative min-h-[210px] bg-[#ede4da]">
      <div class="absolute inset-0">
        <div class="h-full w-full bg-[linear-gradient(120deg,#8b5e3c_0%,#a77d52_42%,#efe7dc_42%,#f8fafc_100%)]"></div>
      </div>
      <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
    </div>

    <div class="relative px-5 pb-5 sm:px-8">
      <div class="-mt-16 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
          <div class="flex h-32 w-32 shrink-0 items-center justify-center rounded-full border-4 border-white bg-[#8b5e3c] text-4xl font-bold text-white shadow-md">
            {{ $initials }}
          </div>
          <div class="pb-2">
            <h1 class="text-3xl font-bold tracking-tight text-slate-950">{{ $displayName }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600">
              <span>{{ $displayEmail }}</span>
              <span class="h-1 w-1 rounded-full bg-slate-300"></span>
              <span>{{ $profile?->phone ?: 'Nomor HP belum diisi' }}</span>
              @if($user->email_verified_at)
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Verified</span>
              @else
                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Belum Verifikasi</span>
              @endif
            </div>
          </div>
        </div>

        <div class="flex flex-wrap gap-2 pb-2">
          <a href="{{ $profileEditUrl }}" class="inline-flex items-center justify-center rounded-lg bg-[#8b5e3c] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#744d31]">
            Perbarui Biodata
          </a>
          @if(Route::has('applications.mine'))
            <a href="{{ route('applications.mine') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
              Lihat Lamaran
            </a>
          @endif
        </div>
      </div>

      <div class="mt-5 grid gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
          <div class="text-xs font-medium text-slate-500">Kelengkapan</div>
          <div class="mt-1 text-2xl font-bold text-slate-950">{{ $profileCompleteness }}%</div>
          <div class="mt-2 h-2 rounded-full bg-white">
            <div class="h-2 rounded-full bg-[#a77d52]" style="width: {{ $profileCompleteness }}%"></div>
          </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
          <div class="text-xs font-medium text-slate-500">Lamaran</div>
          <div class="mt-1 text-2xl font-bold text-slate-950">{{ $user->applications->count() }}</div>
          <div class="mt-1 text-xs text-slate-500">Total posisi yang dilamar</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
          <div class="text-xs font-medium text-slate-500">Pelatihan</div>
          <div class="mt-1 text-2xl font-bold text-slate-950">{{ $profile?->trainings?->count() ?? 0 }}</div>
          <div class="mt-1 text-xs text-slate-500">Data sertifikasi</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
          <div class="text-xs font-medium text-slate-500">Pengalaman</div>
          <div class="mt-1 text-2xl font-bold text-slate-950">{{ $profile?->employments?->count() ?? 0 }}</div>
          <div class="mt-1 text-xs text-slate-500">Riwayat pekerjaan</div>
        </div>
      </div>
    </div>
  </section>

  @if(! $profile)
    <section class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
      <h2 class="text-lg font-semibold">Biodata kandidat belum dibuat</h2>
      <p class="mt-1 text-sm">Lengkapi biodata saat melamar lowongan supaya data lengkap kamu muncul di halaman ini.</p>
      <a href="{{ Route::has('jobs.index') ? route('jobs.index') : url('/jobs') }}" class="mt-4 inline-flex rounded-lg bg-[#8b5e3c] px-4 py-2 text-sm font-semibold text-white">Cari Lowongan</a>
    </section>
  @else
    <div class="mt-6 grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
      <aside class="space-y-6">
        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <h2 class="text-base font-semibold text-slate-950">Intro</h2>
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
          <h2 class="text-base font-semibold text-slate-950">Kontak</h2>
          <div class="mt-4 space-y-3 text-sm">
            <div class="rounded-xl bg-slate-50 p-3">
              <div class="text-xs font-medium text-slate-500">Email</div>
              <div class="mt-1 break-all font-semibold text-slate-900">{{ $displayEmail ?: '-' }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <div class="text-xs font-medium text-slate-500">HP</div>
              <div class="mt-1 font-semibold text-slate-900">{{ $profile->phone ?: '-' }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <div class="text-xs font-medium text-slate-500">WhatsApp</div>
              <div class="mt-1 font-semibold text-slate-900">{{ $profile->whatsapp ?: '-' }}</div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-950">Dokumen</h2>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $documents->count() }}</span>
          </div>
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
          <h2 class="text-base font-semibold text-slate-950">Tentang Kandidat</h2>
          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Gender</div>
              <div class="mt-1 font-semibold capitalize text-slate-950">{{ $profile->gender ?: '-' }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Usia</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->age ? $profile->age . ' tahun' : '-' }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Status pernikahan</div>
              <div class="mt-1 font-semibold capitalize text-slate-950">{{ $profile->status_pernikahan ?: '-' }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Sumber informasi</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->source_channel ? Str::headline($profile->source_channel) : '-' }}</div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <h2 class="text-base font-semibold text-slate-950">Alamat</h2>
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
          <h2 class="text-base font-semibold text-slate-950">Pendidikan & Kesiapan Kerja</h2>
          <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Pendidikan</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->last_education ?: '-' }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Jurusan</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->education_major ?: '-' }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Sekolah / Kampus</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $profile->education_school ?: '-' }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Siap mulai</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $fmtDate($profile->available_start_date) }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Gaji saat ini</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $fmtMoney($profile->current_salary) }}</div>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <div class="text-xs font-medium text-slate-500">Ekspektasi gaji</div>
              <div class="mt-1 font-semibold text-slate-950">{{ $fmtMoney($profile->expected_salary) }}</div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
          <h2 class="text-base font-semibold text-slate-950">Riwayat Lamaran</h2>
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
          <h2 class="text-base font-semibold text-slate-950">Pengalaman Kerja</h2>
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
          <h2 class="text-base font-semibold text-slate-950">Pelatihan & Referensi</h2>
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
      <h2 class="text-base font-semibold text-slate-950">Informasi Akun</h2>
      <div class="mt-4 max-w-xl">
        @include('profile.partials.update-profile-information-form')
      </div>
    </section>

    <section class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
      <h2 class="text-base font-semibold text-slate-950">Keamanan</h2>
      <div class="mt-4 max-w-xl">
        @include('profile.partials.update-password-form')
      </div>
    </section>
  </div>

  <section class="mt-6 rounded-2xl border bg-white p-5 shadow-sm" style="border-color: {{ $BORD }}">
    <h2 class="text-base font-semibold text-slate-950">Hapus Akun</h2>
    <div class="mt-4 max-w-xl">
      @include('profile.partials.delete-user-form')
    </div>
  </section>
</div>
@endsection
