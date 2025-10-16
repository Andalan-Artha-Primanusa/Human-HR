{{-- resources/views/admin/jobs/create.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Create Job' ])

@section('content')
@php
  // Opsi Level & Division dari Model (fallback jika belum didefinisikan)
  $levels = \App\Models\Job::LEVEL_LABELS ?? [
    'bod'=>'BOD','manager'=>'Manager','supervisor'=>'Supervisor','spv'=>'SPV','staff'=>'Staff','non_staff'=>'Non staff'
  ];
  $divisions = \App\Models\Job::DIVISIONS ?? [
    'engineering' => 'Engineering',
    'hr'          => 'Human Resources',
    'it'          => 'Information Technology',
    'finance'     => 'Finance',
    'marketing'   => 'Marketing',
    'sales'       => 'Sales',
    'operations'  => 'Operations',
    'admin'       => 'Administration',
  ];
@endphp

<div class="space-y-6">
  {{-- Header panel ala bar biru–merah (2 tombol saja) --}}
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
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Create Job</h1>
          <div class="mt-1 text-sm text-slate-600 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.jobs.index') }}" class="text-slate-500 hover:text-slate-700">Jobs</a>
            <span class="text-slate-400">/</span>
            <span class="text-slate-700 font-medium">Create</span>
          </div>
        </div>

        <div class="flex gap-2">
          <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost">Kembali</a>
          <button form="jobCreateForm" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Error summary (jika validasi gagal) --}}
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

  {{-- Form utama (tanpa tombol footer) --}}
  <form id="jobCreateForm" class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden"
        method="POST" action="{{ route('admin.jobs.store') }}">
    @csrf

    <div class="p-5 grid gap-4 md:grid-cols-2">
      <div>
        <label class="label">Code <span class="text-red-500">*</span></label>
        <input class="input" name="code" value="{{ old('code') }}" required placeholder="Mis. MCH-OPR-01">
        @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="label">Title <span class="text-red-500">*</span></label>
        <input class="input" name="title" value="{{ old('title') }}" required placeholder="Operator Excavator">
        @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Division --}}
      <div>
        <label class="label">Division</label>
        @php $divisionOld = old('division'); @endphp
        <select class="input" name="division">
          <option value="">— Pilih Division —</option>
          @foreach($divisions as $val => $label)
            <option value="{{ $val }}" @selected($divisionOld === $val)>{{ $label }}</option>
          @endforeach
        </select>
        @error('division')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Site --}}
      <div>
        <label class="label">Site <span class="text-red-500">*</span></label>
        <select class="input" name="site_id" required>
          <option value="">— Pilih Site —</option>
          @forelse($sites as $s)
            <option value="{{ $s->id }}" @selected(old('site_id') == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
          @empty
            <option value="" disabled>Tidak ada data site</option>
          @endforelse
        </select>
        @error('site_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        {{-- legacy support (abaikan saat create) --}}
        <input type="hidden" name="site_code" value="{{ old('site_code') }}">
      </div>

      {{-- Level --}}
      <div>
        <label class="label">Level</label>
        @php $levelOld = old('level'); @endphp
        <select class="input" name="level">
          <option value="">— Pilih Level —</option>
          @foreach($levels as $val => $label)
            <option value="{{ $val }}" @selected($levelOld === $val)>{{ $label }}</option>
          @endforeach
        </select>
        @error('level')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="label">Employment Type <span class="text-red-500">*</span></label>
        @php $et = old('employment_type', 'fulltime'); @endphp
        <select class="input" name="employment_type" required>
          <option value="fulltime" @selected($et==='fulltime')>Fulltime</option>
          <option value="contract" @selected($et==='contract')>Contract</option>
          <option value="intern"   @selected($et==='intern')>Intern</option>
        </select>
        @error('employment_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="label">Openings</label>
        <input class="input" type="number" name="openings" min="1" value="{{ old('openings', 1) }}">
        @error('openings')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="label">Status</label>
        @php $st = old('status', 'open'); @endphp
        <select class="input" name="status">
          <option value="draft"  @selected($st==='draft')>Draft</option>
          <option value="open"   @selected($st==='open')>Open</option>
          <option value="closed" @selected($st==='closed')>Closed</option>
        </select>
        @error('status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Description (Rich Text / Trix) --}}
      <div class="md:col-span-2">
        <label class="label">Description</label>

        {{-- CDN Trix (bold/italic, bullets, numbered list, link, undo/redo) --}}
        @once
          <link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
          <script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
          <style>
            /* selaraskan dengan kelas .input */
            trix-editor {
              border: 1px solid rgb(203 213 225); /* slate-300 */
              border-radius: .5rem;                /* rounded-lg */
              padding: .75rem;                     /* p-3 */
              min-height: 10rem;                   /* ~160px */
              background: #fff;
            }
            trix-toolbar {
              border: 1px solid rgb(203 213 225);
              border-radius: .5rem;
              margin-bottom: .5rem;
            }
            trix-toolbar * { font-size: 0.875rem; }

            /* --- Fix: bullet & numbered list terlihat --- */
            trix-editor ul { list-style: disc; padding-left: 1.25rem; }
            trix-editor ol { list-style: decimal; padding-left: 1.25rem; }
            trix-editor li { margin: .25rem 0; }
          </style>
        @endonce

        {{-- Hidden input yang dikirim ke server --}}
        <input id="desc_input" type="hidden" name="description" value="{{ old('description') }}">
        {{-- Editor yang terhubung ke hidden input --}}
        <trix-editor input="desc_input"></trix-editor>

        <p class="mt-1 text-xs text-slate-500">
          Bisa <strong>bold</strong>, <em>italic</em>, bullet &amp; numbered list, dan tautan. Konten akan disimpan sebagai HTML.
        </p>

        @error('description')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
      </div>
    </div>
  </form>
</div>
@endsection
