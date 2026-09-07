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
      'finance' => 'Finance & Accounting',
        'marketing' => 'Marketing',
        'sales' => 'Sales',
        'operations' => 'Operations',
        'admin' => 'Administration',
    ];

    // Dataset RFR ringkas untuk lookup client-side
    $rfrCompact = collect($rfrVacancies ?? [])->map(function ($rfr) {
        return [
            'code' => $rfr['code'] ?? null,
            'position_ref' => $rfr['position_ref'] ?? null,
            'title' => $rfr['title'] ?? null,
            'department' => $rfr['department'] ?? null,
            'level' => $rfr['level'] ?? $rfr['status_position'] ?? null,
            'site_code' => $rfr['site_code'] ?? $rfr['work_location'] ?? null,
            'company_code' => $rfr['company_code'] ?? null,
            'description' => $rfr['description'] ?? null,
            'facilities' => $rfr['facilities'] ?? null,
            'work_experience' => $rfr['work_experience'] ?? null,
            'education_level' => $rfr['education_level'] ?? null,
            'discipline' => $rfr['discipline'] ?? null,
            'program_study' => $rfr['program_study'] ?? null,
            'candidate_type' => $rfr['candidate_type'] ?? null,
            'qty_required' => (int) ($rfr['qty_required'] ?? 0),
        ];
    })->values()->all();

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
    <style>
      #jobEditForm .label { color: #5c3d1e !important; }
      #jobEditForm .input {
        border-color: #ede4dc !important;
        background:#fff;
      }
      #jobEditForm .input:focus { border-color: #a77d52 !important; box-shadow: 0 0 0 3px rgba(167,125,82,.18) !important; }
      #jobEditForm select.input { background:#fff; }
    </style>
    <div class="mx-auto w-full max-w-[1200px] space-y-6">

      {{-- HEADER (shared solid-brown page-header) --}}
      <x-admin.page-header
        eyebrow="Manajemen Lowongan"
        title="Edit Job"
        description="{{ e($job->title) }} ({{ e($job->code) }})">
        <a href="{{ route('admin.jobs.index') }}" class="ph-action">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Kembali
        </a>
        <button type="submit" form="jobEditForm" class="ph-action ph-action--brand">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Simpan Perubahan
        </button>
      </x-admin.page-header>

      {{-- Info unik per company --}}
      <div class="rounded-xl bg-white text-[#7a5236] px-4 py-3 border text-sm" style="border-color: {{ $BORD }}">
        Kode lowongan (<code class="font-mono">code</code>) unik <strong>per company</strong>. Mengubah Company dapat
        mempengaruhi keunikan kode.
      </div>

      {{-- Tarik ulang data dari RFR MinePro --}}
      <div class="rounded-xl bg-white border px-4 py-3 text-sm" style="border-color: {{ $BORD }}">
        <div class="flex flex-wrap items-center gap-2">
          <div class="font-semibold text-[#5c3d1e]">Ambil ulang dari RFR MinePro</div>
          <input type="text" id="rfr_ref" class="input flex-1 min-w-[220px]"
                 placeholder="Tempel RFRRefID / Position_Ref. Mis. 126" style="--tw-ring-color: {{ $ACCENT }}">
          <button type="button" id="rfr_apply_btn"
                  class="inline-flex items-center rounded-lg bg-[#a77d52] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
            Terapkan
          </button>
        </div>
        <p class="mt-1 text-xs text-emerald-700" id="rfr_status"></p>
        <p class="mt-1 text-[11px] text-slate-400">
          @if(empty($rfrCompact))
            Data RFR belum tersedia untuk bulan ini — field tetap bisa diedit manual.
          @else
            {{ count($rfrCompact) }} RFR tersedia bulan ini. Tempel RFRRefID / Position_Ref lalu Terapkan untuk mengisi ulang field dari API.
          @endif
        </p>
      </div>

      {{-- Error summary ditangani AJAX/modal global (KarirFeedback) --}}

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

      // ==== Tarik ulang dari RFR MinePro ====
      const rfrInput    = document.getElementById('rfr_ref');
      const rfrApplyBtn = document.getElementById('rfr_apply_btn');
      const rfrStatus   = document.getElementById('rfr_status');
      const rfrList     = @json($rfrCompact);
      const codeEl      = document.querySelector('[name="code"]');
      const titleEl     = document.querySelector('[name="title"]');
      const divisionEl  = document.querySelector('[name="division"]');
      const levelEl     = document.querySelector('[name="level"]');
      const descInput   = document.getElementById('desc_input');
      const isFreshEdit = @json(old('code', null) === null);

      function normalizeOptionValue(raw) {
        return (raw || '')
          .toString().trim().toLowerCase()
          .replace(/&/g, 'and')
          .replace(/[^a-z0-9]+/g, '_')
          .replace(/^_+|_+$/g, '');
      }

      function setSelectByNormalized(select, raw) {
        if (!select || !raw) return;
        const target = normalizeOptionValue(raw);
        const match = Array.from(select.options).find((opt) =>
          normalizeOptionValue(opt.value) === target
          || normalizeOptionValue(opt.textContent) === target
          || normalizeOptionValue(opt.textContent).includes(target)
        );
        if (match) select.value = match.value;
      }

      function setSiteByCode(rawCode) {
        if (!siteSel || !rawCode) return;
        const target = rawCode.toString().trim().toLowerCase();
        if (siteCode) siteCode.value = rawCode.toString().trim();
        const match = Array.from(siteSel.options).find((opt) =>
          (opt.dataset.code || '').toLowerCase() === target
          || opt.textContent.toLowerCase().includes(target)
        );
        if (match) {
          siteSel.value = match.value;
          syncSiteCode();
          siteSel.setAttribute('required', 'required');
        } else {
          const existing = siteSel.querySelector('option[data-api-site="1"]');
          if (existing) existing.remove();
          const apiOption = new Option(`${rawCode.toString().trim()} `, '');
          apiOption.dataset.apiSite = '1';
          siteSel.add(apiOption);
          apiOption.selected = true;
          siteSel.removeAttribute('required');
        }
      }

      function setTrixDescription(text) {
        if (!descInput || !text) return;
        const html = text
          .toString()
          .split(/\n{2,}/)
          .map((part) => part.trim())
          .filter(Boolean)
          .map((part) => `<p>${part.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>')}</p>`)
          .join('');
        descInput.value = html;
        const editor = document.querySelector('trix-editor[input="desc_input"]');
        if (editor?.editor) editor.editor.loadHTML(html);
      }

      function applyRfr(rfr) {
        if (!rfr) return;
        if (codeEl) codeEl.value = rfr.code || '';
        if (titleEl && rfr.title) titleEl.value = rfr.title;
        setSelectByNormalized(divisionEl, rfr.department);
        setSelectByNormalized(levelEl, rfr.level);
        setSiteByCode(rfr.site_code);
        if (compCode && rfr.company_code) {
          if (compSel) compSel.value = '';
          compCode.removeAttribute('disabled');
          compCode.value = rfr.company_code;
          toggleCompanyInputs();
        }
        setTrixDescription(rfr.description);

        const keywordParts = [
          rfr.title, rfr.department, rfr.site_code,
          rfr.education_level, rfr.discipline, rfr.program_study, rfr.candidate_type,
        ].filter(Boolean);
        if (kw && keywordParts.length) {
          kw.value = keywordParts.join(', ');
          kw.dispatchEvent(new Event('input'));
        }

        const skillParts = [
          rfr.work_experience ? `Pengalaman ${rfr.work_experience}` : '',
          rfr.education_level ? `Pendidikan ${rfr.education_level}` : '',
          rfr.discipline ? `Disiplin ${rfr.discipline}` : '',
          rfr.program_study ? `Program ${rfr.program_study}` : '',
          rfr.facilities ? `Fasilitas ${rfr.facilities}` : '',
        ].filter(Boolean);
        if (skills && skillParts.length) skills.value = skillParts.join(', ');

        if (rfrStatus) {
          const ref = rfr.position_ref ? ` · Position_Ref #${rfr.position_ref}` : '';
          rfrStatus.textContent = `Diterapkan: ${rfr.code || '-'}${ref} — ${rfr.title || 'Tanpa posisi'}`;
          rfrStatus.classList.add('text-emerald-700');
          rfrStatus.classList.remove('text-rose-600');
        }
      }

      function findRfr(query) {
        const q = (query || '').toString().trim().toLowerCase();
        if (!q) return null;
        return rfrList.find((r) =>
          (r.code || '').toLowerCase() === q
          || String(r.position_ref ?? '').toLowerCase() === q
          || (r.title || '').toLowerCase().includes(q)
        ) || null;
      }

      function applyRfrFromInput() {
        if (!rfrInput || !rfrInput.value.trim()) return;
        const rfr = findRfr(rfrInput.value);
        if (rfr) {
          applyRfr(rfr);
          rfrInput.value = rfr.code || '';
        } else if (rfrStatus) {
          rfrStatus.textContent = `Tidak ada RFR dengan kode/ref “${rfrInput.value.trim()}” untuk bulan ini.`;
          rfrStatus.classList.remove('text-emerald-700');
          rfrStatus.classList.add('text-rose-600');
        }
      }

      rfrApplyBtn?.addEventListener('click', applyRfrFromInput);
      rfrInput?.addEventListener('keydown', function(e){
        if (e.key === 'Enter') { e.preventDefault(); applyRfrFromInput(); }
      });
      rfrInput?.addEventListener('blur', applyRfrFromInput);

      // Auto-terisi dari RFR saat edit halaman fresh (code job cocok dengan API)
      if (isFreshEdit && rfrList.length && codeEl) {
        const matched = findRfr(codeEl.value);
        if (matched) applyRfr(matched);
      }
    })();
    </script>
@endsection
