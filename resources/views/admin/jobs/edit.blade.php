{{-- resources/views/admin/jobs/edit.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Edit Job' ])

@section('content')
@php
  $levels = \App\Models\Job::LEVEL_LABELS ?? [
    'bod'=>'BOD','manager'=>'Manager','supervisor'=>'Supervisor','spv'=>'SPV','staff'=>'Staff','non_staff'=>'Non staff'
  ];
  $divisions = \App\Models\Job::DIVISIONS ?? [
    'engineering'=>'Engineering','hr'=>'Human Resources','it'=>'Information Technology','finance'=>'Finance',
    'marketing'=>'Marketing','sales'=>'Sales','operations'=>'Operations','admin'=>'Administration',
  ];
  $val = fn($key, $fallback = null) => old($key, $fallback);
@endphp

<div class="space-y-6">
  {{-- Header --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 90%"></div>
        <div class="h-full bg-red-500"  style="width: 10%"></div>
      </div>
    </div>

    <div class="p-6 md:p-7">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">
            Edit Job: {{ $job->title }} <span class="text-slate-400">({{ $job->code }})</span>
          </h1>
          <div class="mt-1 text-sm text-slate-600 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.jobs.index') }}" class="text-slate-500 hover:text-slate-700">Jobs</a>
            <span class="text-slate-400">/</span>
            <span class="text-slate-700 font-medium">Edit</span>
          </div>
        </div>

        <div class="flex gap-2">
          <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost">Kembali</a>
          <button form="jobEditForm" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Info unik per company --}}
  <div class="rounded-xl bg-sky-50 text-sky-800 px-4 py-3 border border-sky-200 text-sm">
    Kode lowongan (<code class="font-mono">code</code>) unik <strong>per company</strong>. Mengganti Company dapat
    mempengaruhi keunikan kode.
  </div>

  {{-- Error summary --}}
  @if ($errors->any())
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 border border-red-200">
      <div class="font-medium">Periksa kembali isian Anda:</div>
      <ul class="mt-1 list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Form --}}
  <form id="jobEditForm" class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden"
        method="POST" action="{{ route('admin.jobs.update', $job) }}">
    @csrf @method('PUT')

    <div class="p-5 grid gap-4 md:grid-cols-2">
      {{-- Code --}}
      <div>
        <label class="label">Code <span class="text-red-500">*</span></label>
        <input class="input" name="code" value="{{ $val('code', $job->code) }}" required>
        @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Title --}}
      <div>
        <label class="label">Title <span class="text-red-500">*</span></label>
        <input class="input" name="title" value="{{ $val('title', $job->title) }}" required>
        @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Division --}}
      <div>
        <label class="label">Division</label>
        @php $divisionVal = $val('division', $job->division); @endphp
        <select class="input" name="division">
          <option value="">— Pilih Division —</option>
          @foreach($divisions as $slug => $label)
            <option value="{{ $slug }}" @selected($divisionVal === $slug)>{{ $label }}</option>
          @endforeach
        </select>
        @error('division')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Site --}}
      <div>
        <label class="label">Site <span class="text-red-500">*</span></label>
        @php $siteVal = $val('site_id', $job->site_id); @endphp
        <select class="input" name="site_id" required>
          <option value="">— Pilih Site —</option>
          @forelse($sites as $s)
            <option value="{{ $s->id }}" @selected((string)$siteVal === (string)$s->id)>{{ $s->code }} — {{ $s->name }}</option>
          @empty
            <option value="" disabled>Tidak ada data site</option>
          @endforelse
        </select>
        @error('site_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        {{-- legacy support via code --}}
        <input type="hidden" name="site_code" value="{{ old('site_code') }}">
      </div>

      {{-- Company (opsional) --}}
      <div class="md:col-span-2 grid md:grid-cols-2 gap-4">
        <div>
          <label class="label">Company (opsional)</label>
          @php $companyVal = $val('company_id', $job->company_id); @endphp
          <select class="input" name="company_id">
            <option value="">— Tidak ada company —</option>
            @forelse(($companies ?? []) as $c)
              <option value="{{ $c->id }}" @selected((string)$companyVal === (string)$c->id)>{{ $c->code }} — {{ $c->name }}</option>
            @empty
              <option value="" disabled>Tidak ada data company</option>
            @endforelse
          </select>
          @error('company_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="label">Company Code (opsional)</label>
          <input class="input" name="company_code" value="{{ old('company_code') }}" placeholder="mis. ACME">
          <p class="text-xs text-slate-500 mt-1">Isi salah satu: <code>Company</code> (dropdown) <em>atau</em> <code>Company Code</code>.</p>
          @error('company_code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      {{-- Level --}}
      <div>
        <label class="label">Level</label>
        @php $levelVal = $val('level', $job->level); @endphp
        <select class="input" name="level">
          <option value="">— Pilih Level —</option>
          @foreach($levels as $slug => $label)
            <option value="{{ $slug }}" @selected($levelVal === $slug)>{{ $label }}</option>
          @endforeach
        </select>
        @error('level')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Employment Type --}}
      <div>
        <label class="label">Employment Type <span class="text-red-500">*</span></label>
        @php $et = $val('employment_type', $job->employment_type ?? 'fulltime'); @endphp
        <select class="input" name="employment_type" required>
          <option value="fulltime" @selected($et==='fulltime')>Fulltime</option>
          <option value="contract" @selected($et==='contract')>Contract</option>
          <option value="intern"   @selected($et==='intern')>Intern</option>
        </select>
        @error('employment_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Openings (disinkron dari Manpower) --}}
      <div>
        <label class="label">Openings</label>
        <input class="input" type="number" name="openings" min="1" value="{{ (int) $job->openings }}" disabled>
        <p class="text-xs text-slate-500 mt-1">Nilai ini disinkron otomatis dari <em>Manpower Requirements</em>.</p>
        @error('openings')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Status --}}
      <div>
        <label class="label">Status</label>
        @php $st = $val('status', $job->status ?? 'open'); @endphp
        <select class="input" name="status">
          <option value="draft"  @selected($st==='draft')>Draft</option>
          <option value="open"   @selected($st==='open')>Open</option>
          <option value="closed" @selected($st==='closed')>Closed</option>
        </select>
        @error('status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Description --}}
      <div class="md:col-span-2">
        <label class="label">Description</label>
        <textarea class="input min-h-[160px]" name="description" placeholder="Ringkasan pekerjaan, kualifikasi, benefit, dsb.">{{ $val('description', $job->description) }}</textarea>
        @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </div>
  </form>
</div>
@endsection
