{{-- resources/views/admin/jobs/edit.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Edit Job'])

@php
    // THEME
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200

    $levels = \App\Models\Job::LEVEL_LABELS ?? [
        'bod' => 'BOD',
        'manager' => 'Manager',
        'supervisor' => 'Supervisor',
        'spv' => 'SPV',
        'staff' => 'Staff',
        'non_staff' => 'Non staff'
    ];
    $divisions = \App\Models\Job::DIVISIONS ?? [
        'engineering' => 'Engineering',
        'hr' => 'Human Resources',
        'it' => 'Information Technology',
        'finance' => 'Finance',
        'marketing' => 'Marketing',
        'sales' => 'Sales',
        'operations' => 'Operations',
        'admin' => 'Administration',
    ];

    // Helpers
    $val = fn($key, $fallback = null) => old($key, $fallback);
    $toStr = function ($v) {
        if (is_array($v))
            return implode(', ', array_map('strval', array_filter($v, fn($x) => $x !== null && $x !== '')));
        if (is_object($v))
            return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return (string) ($v ?? '');
    };
@endphp

@section('content')
    <div class="space-y-6">

      {{-- HEADER dua-tone + tombol aksi --}}
      <section class="relative bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative h-20 overflow-hidden sm:h-24 rounded-t-2xl">
          <div class="absolute inset-0 rounded-t-2xl" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
          <div class="absolute inset-y-0 right-0 w-24 rounded-tr-2xl sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

          <div class="relative flex flex-col h-full gap-3 px-5 md:px-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white md:text-3xl">
                Edit Job: {{ e($job->title) }} <span class="opacity-70">({{ e($job->code) }})</span>
              </h1>
              <div class="flex flex-wrap items-center gap-2 mt-1 text-xs sm:text-sm text-white/90">
                <a href="{{ route('admin.jobs.index') }}" class="hover:text-white">Jobs</a>
                <span class="opacity-70">/</span>
                <span class="font-medium text-white">Edit</span>
              </div>
            </div>

            <div class="flex gap-2">
              <a href="{{ route('admin.jobs.index') }}"
                 class="inline-flex items-center px-4 py-2 text-sm font-semibold bg-white border rounded-lg border-slate-200 text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
                 style="--tw-ring-color: {{ $ACCENT }}">Kembali</a>
              <button form="jobEditForm"
                      class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold text-white bg-[#a77d52] hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2"
                      style="--tw-ring-color: {{ $ACCENT }}">Simpan Perubahan</button>
            </div>
          </div>
        </div>
      </section>

      {{-- Info unik per company --}}
      <div class="rounded-xl bg-white text-[#7a5236] px-4 py-3 border text-sm" style="border-color: {{ $BORD }}">
        Kode lowongan (<code class="font-mono">code</code>) unik <strong>per company</strong>. Mengubah Company dapat
        mempengaruhi keunikan kode.
      </div>

      {{-- Error summary --}}
      @if ($errors->any())
        <div class="px-4 py-3 border rounded-xl bg-rose-50 text-rose-700" style="border-color:#fecaca">
          <div class="font-medium">Periksa kembali isian Anda:</div>
          <ul class="mt-1 text-sm list-disc list-inside">
            @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- FORM utama --}}
      <form id="jobEditForm" class="overflow-hidden bg-white border shadow-sm rounded-2xl"
            style="border-color: {{ $BORD }}"
            method="POST" action="{{ route('admin.jobs.update', $job) }}" novalidate>
        @csrf @method('PUT')

        <div class="p-6 md:p-7 grid gap-4 md:grid-cols-2 bg-white">
          {{-- Code --}}
          <div>
            <label class="label">Code <span class="text-rose-600">*</span></label>
            <input class="input" name="code" value="{{ $toStr($val('code', $job->code)) }}" required maxlength="50"
               style="--tw-ring-color: {{ $ACCENT }}">
            @error('code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Title --}}
          <div>
            <label class="label">Title <span class="text-rose-600">*</span></label>
            <input class="input" name="title" value="{{ $toStr($val('title', $job->title)) }}" required maxlength="200"
               style="--tw-ring-color: {{ $ACCENT }}">
            @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Division --}}
          <div>
            <label class="label">Division</label>
            @php $divisionVal = $val('division', $job->division); @endphp
            <select class="input" name="division" style="--tw-ring-color: {{ $ACCENT }}">
              <option value="">— Pilih Division —</option>
              @foreach($divisions as $slug => $label)
                <option value="{{ $slug }}" @selected($divisionVal === $slug)>{{ $label }}</option>
              @endforeach
            </select>
            @error('division')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Site --}}
          <div>
            <label class="label">Site <span class="text-rose-600">*</span></label>
            @php $siteVal = $val('site_id', $job->site_id); @endphp
            <select class="input" name="site_id" id="site_id" required style="--tw-ring-color: {{ $ACCENT }}">
              <option value="">— Pilih Site —</option>
              @forelse($sites as $site)
                <option value="{{ $site->id }}" data-code="{{ $site->code }}"
                  @selected((string) $siteVal === (string) $site->id)>{{ $site->code }} — {{ $site->name }}</option>
              @empty
                <option value="" disabled>Tidak ada data site</option>
              @endforelse
            </select>
            {{-- legacy support via code --}}
            <input type="hidden" name="site_code" id="site_code" value="{{ $toStr(old('site_code')) }}">
            <p class="mt-1 text-xs text-slate-500">Bisa pilih via dropdown (site_id) atau kirim <code>site_code</code>.</p>
            @error('site_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            @error('site_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Employment Type --}}
          <div>
            <label class="label">Employment Type <span class="text-rose-600">*</span></label>
            @php $et = $val('employment_type', $job->employment_type ?? 'fulltime'); @endphp
            <select class="input" name="employment_type" required style="--tw-ring-color: {{ $ACCENT }}">
              <option value="fulltime" @selected($et === 'fulltime')>Fulltime</option>
              <option value="contract" @selected($et === 'contract')>Contract</option>
              <option value="intern"   @selected($et === 'intern')>Intern</option>
            </select>
            @error('employment_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Company (opsional) + Company Code --}}
          <div class="grid gap-4 md:col-span-2 md:grid-cols-2">
            <div>
              <label class="label">Company (opsional)</label>
              @php $companyVal = $val('company_id', $job->company_id); @endphp
              <select class="input" name="company_id" id="company_id" style="--tw-ring-color: {{ $ACCENT }}">
                <option value="">— Tidak ada company —</option>
                @forelse(($companies ?? []) as $company)
                      <option value="{{ data_get($company, 'id') }}" data-code="{{ data_get($company, 'code') }}"
                        @selected((string) $companyVal === (string) data_get($company, 'id'))>{{ data_get($company, 'code') }} — {{ data_get($company, 'name') }}</option>
                @empty
                      <option value="" disabled>Tidak ada data company</option>
                @endforelse
              </select>
              @error('company_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
              <label class="label">Company Code (opsional)</label>
              <input class="input" name="company_code" id="company_code"
                     value="{{ $toStr(old('company_code')) }}" maxlength="50" placeholder="mis. ACME"
                   style="--tw-ring-color: {{ $ACCENT }}">
              <p class="mt-1 text-xs text-slate-500">Isi salah satu: <code>Company</code> (dropdown) <em>atau</em> <code>Company Code</code>.</p>
              @error('company_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
          </div>

          {{-- Level --}}
          <div>
            <label class="label">Level</label>
            @php $levelVal = $val('level', $job->level); @endphp
            <select class="input" name="level" style="--tw-ring-color: {{ $ACCENT }}">
              <option value="">— Pilih Level —</option>
              @foreach($levels as $slug => $label)
                <option value="{{ $slug }}" @selected($levelVal === $slug)>{{ $label }}</option>
              @endforeach
            </select>
            @error('level')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Openings (info) --}}
          <div>
            <label class="label">Openings</label>
            <input class="input" type="number" min="0" value="{{ (int) $job->openings }}" disabled
               style="--tw-ring-color: {{ $ACCENT }}">
            <p class="mt-1 text-xs text-slate-500">Nilai ini disinkron otomatis dari <em>Manpower Requirements</em>.</p>
          </div>

          {{-- Status --}}
          <div>
            <label class="label">Status</label>
            @php $st = $val('status', $job->status ?? 'open'); @endphp
            <select class="input" name="status" style="--tw-ring-color: {{ $ACCENT }}">
              <option value="draft"  @selected($st === 'draft')>Draft</option>
              <option value="open"   @selected($st === 'open')>Open</option>
              <option value="closed" @selected($st === 'closed')>Closed</option>
            </select>
            @error('status')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Keywords --}}
          <div class="md:col-span-2">
            <label class="label">Keywords (SEO internal)</label>
            <input class="input" name="keywords" id="keywords" maxlength="500"
                   value="{{ $toStr($val('keywords', $job->keywords)) }}"
                   placeholder="contoh: excavator, operator alat berat, tambang"
               style="--tw-ring-color: {{ $ACCENT }}">
            <div class="flex items-center justify-between mt-1 text-xs text-slate-500">
              <span>Pisahkan dengan koma untuk memudahkan pencarian.</span>
              <span id="kw_count">0/500</span>
            </div>
            @error('keywords')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Skills --}}
          <div class="md:col-span-2">
            <label class="label">Skills</label>
            @php
                $skillsDisplay = is_array($job->skills) ? implode(', ', $job->skills) : ($job->skills ?? '');
            @endphp
            <textarea class="input min-h-[84px]" name="skills" id="skills"
                      placeholder="Ketik skill, pisahkan dengan koma atau Enter. Contoh: Excavator A40, SIM B2 Umum, Basic Safety"
                      style="--tw-ring-color: {{ $ACCENT }}">{{ $toStr($val('skills', $skillsDisplay)) }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Boleh diisi: <em>comma-separated</em> atau satu skill per baris. Akan dinormalkan.</p>
            @error('skills')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Description (Trix, set via JS supaya aman jika old() array) --}}
          <div class="md:col-span-2">
            <label class="label">Description</label>

            @once
                  <link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
                  <script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
                  <style>
                    trix-editor{
                      border:1px solid {{ $BORD }};
                      border-radius:.5rem; padding:.75rem; min-height:10rem; background:#fff;
                    }
                    trix-toolbar{ border:1px solid {{ $BORD }}; border-radius:.5rem; margin-bottom:.5rem; }
                    trix-toolbar *{ font-size:.875rem }
                    trix-editor ul{ list-style:disc; padding-left:1.25rem }
                    trix-editor ol{ list-style:decimal; padding-left:1.25rem }
                    trix-editor li{ margin:.25rem 0 }
                  </style>
            @endonce

            <input id="desc_input" type="hidden" name="description">
            <trix-editor input="desc_input"></trix-editor>

            <p class="mt-1 text-xs text-slate-500">
              Bisa <strong>bold</strong>, <em>italic</em>, bullet & numbered list, dan tautan. Konten disimpan sebagai HTML.
            </p>
            @error('description')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>
        </div>
      </form>
    </div>

    {{-- Helpers & UX scripts --}}
    <script>
    (function(){
      const siteSel  = document.getElementById('site_id');
      const siteCode = document.getElementById('site_code');
      const compSel  = document.getElementById('company_id');
      const compCode = document.getElementById('company_code');
      const kw       = document.getElementById('keywords');
      const skills   = document.getElementById('skills');
      const form     = document.getElementById('jobEditForm');
      const kwCount  = document.getElementById('kw_count');

      // Keywords counter
      if (kw && kwCount) {
        const updateKw = () => kwCount.textContent = (kw.value?.length||0) + '/500';
        kw.addEventListener('input', updateKw); updateKw();
      }

      // site_code sinkron dari opsi site (pakai data-code)
      function syncSiteCode(){
        const opt  = siteSel?.options[siteSel.selectedIndex];
        const code = opt?.getAttribute?.('data-code') || '';
        siteCode.value = code;
      }
      siteSel?.addEventListener('change', syncSiteCode);
      syncSiteCode();

      // company_id ↔ company_code (Rule: prohibits)
      function toggleCompanyInputs(){
        const hasDropdown = !!compSel?.value;
        const hasManual   = !!compCode?.value.trim();

        if (hasDropdown) {
          compCode.value = '';
          compCode.setAttribute('disabled', 'disabled');
          compCode.classList.add('bg-slate-50','cursor-not-allowed');
        } else {
          compCode.removeAttribute('disabled');
          compCode.classList.remove('bg-slate-50','cursor-not-allowed');
        }
        if (hasManual) compSel.value = '';
      }
      compSel?.addEventListener('change', toggleCompanyInputs);
      compCode?.addEventListener('input', toggleCompanyInputs);
      toggleCompanyInputs();

      // Normalize skills on submit: comma/newline → "a, b, c"
      form?.addEventListener('submit', function(){
        if (skills && skills.value.trim().length){
          let raw = skills.value.split(/[\n,]/g).map(s => s.trim()).filter(Boolean);
          skills.value = raw.join(', ');
        }
      }, {passive:true});

      // Inisialisasi description (aman terhadap array/object)
      const hidden = document.getElementById('desc_input');
      try{
        const initialDesc = @json(old('description', $job->description ?? ''));
        hidden.value = (typeof initialDesc === 'string') ? initialDesc : JSON.stringify(initialDesc);
      }catch(_){
        hidden.value = '';
      }
    })();
    </script>
@endsection
