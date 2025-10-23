{{-- resources/views/admin/jobs/create.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Create Job' ])

@php
  // THEME
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200

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

@section('content')
<div class="space-y-6">

  {{-- HEADER dua-tone (2 tombol) --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-white">Create Job</h1>
          <div class="mt-1 text-xs sm:text-sm text-white/90 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.jobs.index') }}" class="hover:text-white">Jobs</a>
            <span class="opacity-70">/</span>
            <span class="font-medium text-white">Create</span>
          </div>
        </div>
        <div class="flex gap-2">
          <a href="{{ route('admin.jobs.index') }}"
             class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
             style="--tw-ring-color: {{ $BLUE }}">Kembali</a>
          <button form="jobCreateForm"
                  class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2"
                  style="--tw-ring-color: {{ $BLUE }}">Simpan</button>
        </div>
      </div>
    </div>
  </section>

  {{-- Info unik per company --}}
  <div class="rounded-xl bg-sky-50 text-sky-800 px-4 py-3 border text-sm" style="border-color: {{ $BORD }}">
    Kode lowongan (<code class="font-mono">code</code>) unik <strong>per company</strong>. Kamu boleh kosongkan Company bila job tidak terikat company tertentu.
  </div>

  {{-- Error summary --}}
  @if ($errors->any())
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 border" style="border-color: #fecaca">
      <div class="font-medium">Periksa kembali isian Anda:</div>
      <ul class="mt-1 list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM utama (tanpa footer button, tombol di header) --}}
  <form id="jobCreateForm" class="rounded-2xl border bg-white shadow-sm overflow-hidden" style="border-color: {{ $BORD }}"
        method="POST" action="{{ route('admin.jobs.store') }}">
    @csrf

    <div class="p-5 grid gap-4 md:grid-cols-2">
      <div>
        <label class="label">Code <span class="text-rose-600">*</span></label>
        <input class="input" name="code" value="{{ old('code') }}" required placeholder="Mis. MCH-OPR-01"
               style="--tw-ring-color: {{ $BLUE }}">
        @error('code')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="label">Title <span class="text-rose-600">*</span></label>
        <input class="input" name="title" value="{{ old('title') }}" required placeholder="Operator Excavator"
               style="--tw-ring-color: {{ $BLUE }}">
        @error('title')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Division --}}
      <div>
        <label class="label">Division</label>
        @php $divisionOld = old('division'); @endphp
        <select class="input" name="division" style="--tw-ring-color: {{ $BLUE }}">
          <option value="">— Pilih Division —</option>
          @foreach($divisions as $val => $label)
            <option value="{{ $val }}" @selected($divisionOld === $val)>{{ $label }}</option>
          @endforeach
        </select>
        @error('division')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Site --}}
      <div>
        <label class="label">Site <span class="text-rose-600">*</span></label>
        <select class="input" name="site_id" required style="--tw-ring-color: {{ $BLUE }}">
          <option value="">— Pilih Site —</option>
          @forelse($sites as $s)
            <option value="{{ $s->id }}" @selected(old('site_id') == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
          @empty
            <option value="" disabled>Tidak ada data site</option>
          @endforelse
        </select>
        @error('site_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        <input type="hidden" name="site_code" value="{{ old('site_code') }}">
      </div>

      {{-- Company (opsional) + Company code --}}
      <div class="md:col-span-2 grid md:grid-cols-2 gap-4">
        <div>
          <label class="label">Company (opsional)</label>
          <select class="input" name="company_id" style="--tw-ring-color: {{ $BLUE }}">
            <option value="">— Tidak ada company —</option>
            @forelse(($companies ?? []) as $c)
              <option value="{{ $c->id }}" @selected(old('company_id') == $c->id)>{{ $c->code }} — {{ $c->name }}</option>
            @empty
              <option value="" disabled>Tidak ada data company</option>
            @endforelse
          </select>
          @error('company_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="label">Company Code (opsional)</label>
          <input class="input" name="company_code" value="{{ old('company_code') }}" placeholder="mis. ACME"
                 style="--tw-ring-color: {{ $BLUE }}">
          <p class="text-xs text-slate-500 mt-1">Isi salah satu: <code>Company</code> (dropdown) <em>atau</em> <code>Company Code</code>.</p>
          @error('company_code')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      {{-- Level --}}
      <div>
        <label class="label">Level</label>
        @php $levelOld = old('level'); @endphp
        <select class="input" name="level" style="--tw-ring-color: {{ $BLUE }}">
          <option value="">— Pilih Level —</option>
          @foreach($levels as $val => $label)
            <option value="{{ $val }}" @selected($levelOld === $val)>{{ $label }}</option>
          @endforeach
        </select>
        @error('level')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Employment Type --}}
      <div>
        <label class="label">Employment Type <span class="text-rose-600">*</span></label>
        @php $et = old('employment_type', 'fulltime'); @endphp
        <select class="input" name="employment_type" required style="--tw-ring-color: {{ $BLUE }}">
          <option value="fulltime" @selected($et==='fulltime')>Fulltime</option>
          <option value="contract" @selected($et==='contract')>Contract</option>
          <option value="intern"   @selected($et==='intern')>Intern</option>
        </select>
        @error('employment_type')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Openings (disabled) --}}
      <div>
        <label class="label">Openings</label>
        <input class="input" type="number" name="openings" min="1" value="{{ old('openings', 1) }}" disabled
               style="--tw-ring-color: {{ $BLUE }}">
        <p class="text-xs text-slate-500 mt-1">Nilai ini akan disinkron otomatis dari <em>Manpower Requirements</em>.</p>
        @error('openings')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Status --}}
      <div>
        <label class="label">Status</label>
        @php $st = old('status', 'open'); @endphp
        <select class="input" name="status" style="--tw-ring-color: {{ $BLUE }}">
          <option value="draft"  @selected($st==='draft')>Draft</option>
          <option value="open"   @selected($st==='open')>Open</option>
          <option value="closed" @selected($st==='closed')>Closed</option>
        </select>
        @error('status')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Description (Trix) --}}
      <div class="md:col-span-2">
        <label class="label">Description</label>

        @once
          <link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
          <script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
          <style>
            trix-editor{
              border:1px solid {{ $BORD }};
              border-radius:.5rem;
              padding:.75rem;
              min-height:10rem;
              background:#fff;
            }
            trix-toolbar{
              border:1px solid {{ $BORD }};
              border-radius:.5rem;
              margin-bottom:.5rem;
            }
            trix-toolbar *{ font-size:.875rem }
            trix-editor ul{ list-style:disc; padding-left:1.25rem }
            trix-editor ol{ list-style:decimal; padding-left:1.25rem }
            trix-editor li{ margin:.25rem 0 }
          </style>
        @endonce

        <input id="desc_input" type="hidden" name="description" value="{{ old('description') }}">
        <trix-editor input="desc_input"></trix-editor>

        <p class="mt-1 text-xs text-slate-500">
          Bisa <strong>bold</strong>, <em>italic</em>, bullet & numbered list, dan tautan. Konten disimpan sebagai HTML.
        </p>
        @error('description')<p class="text-xs text-rose-600 mt-2">{{ $message }}</p>@enderror
      </div>
    </div>
  </form>
</div>
@endsection
