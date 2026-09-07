{{-- resources/views/admin/jobs/create.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Create Job'])

@php
    // THEME
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200

    // Opsi Level & Division dari Model (fallback)
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

    // Dataset RFR ringkas untuk lookup client-side (ISO string → readable)
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
            'api_row_no' => $rfr['api_row_no'] ?? null,
        ];
    })->values()->all();
@endphp

@section('content')
    <style>
      #jobCreateForm .label { color: #5c3d1e !important; }
      #jobCreateForm .input {
        border-color: #ede4dc !important;
        background:#fff;
      }
      #jobCreateForm .input:focus { border-color: #a77d52 !important; box-shadow: 0 0 0 3px rgba(167,125,82,.18) !important; }
      #jobCreateForm select.input { background:#fff; }
    </style>
    <div class="mx-auto w-full max-w-[1200px] space-y-6">

      {{-- HEADER (shared solid-brown page-header) --}}
      <x-admin.page-header
        eyebrow="Manajemen Lowongan"
        title="Create Job"
        description="Buat lowongan baru dengan detail posisi, site, dan kompensasi.">
        <a href="{{ route('admin.jobs.index') }}" class="ph-action">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Kembali
        </a>
        <button type="submit" form="jobCreateForm" class="ph-action ph-action--brand">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Simpan
        </button>
      </x-admin.page-header>

      {{-- Info unik per company --}}
      <div class="rounded-xl bg-white text-[#7a5236] px-4 py-3 border text-sm" style="border-color: {{ $BORD }}">
        Kode lowongan (<code class="font-mono">code</code>) unik <strong>per company</strong>. Kamu boleh kosongkan Company bila job tidak terikat company tertentu.
      </div>

      <form method="GET" action="{{ route('admin.jobs.create') }}"
            class="rounded-xl bg-white text-[#7a5236] px-4 py-3 border text-sm flex flex-wrap items-end gap-3"
            style="border-color: {{ $BORD }}">
        <div>
          <label class="label">StartDate RFR</label>
          <input type="date" name="rfr_start_date" value="{{ $rfrStartDate ?? now()->startOfMonth()->format('Y-m-d') }}"
                 class="input min-w-[180px]" style="--tw-ring-color: {{ $ACCENT }}">
        </div>
        <div>
          <label class="label">EndDate RFR</label>
          <input type="date" name="rfr_end_date" value="{{ $rfrEndDate ?? now()->endOfMonth()->format('Y-m-d') }}"
                 min="{{ $rfrStartDate ?? now()->startOfMonth()->format('Y-m-d') }}"
                 class="input min-w-[180px]" style="--tw-ring-color: {{ $ACCENT }}">
        </div>
        <button type="submit"
                class="inline-flex items-center rounded-lg bg-[#a77d52] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
          Ambil RFR
        </button>
        <div class="text-xs text-slate-500">
          Pilih periode RFR MinePro dulu, lalu pilih RFR dari dropdown.
        </div>
      </form>

      {{-- Error summary --}}
      @if ($errors->any())
        <div class="px-4 py-3 border rounded-xl bg-rose-50 text-rose-700" style="border-color: #fecaca">
          <div class="font-medium">Periksa kembali isian Anda:</div>
          <ul class="mt-1 text-sm list-disc list-inside">
            @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- FORM utama --}}
      <form id="jobCreateForm" class="overflow-hidden bg-white border shadow-sm rounded-2xl"
            style="border-color: {{ $BORD }}"
            method="POST" action="{{ route('admin.jobs.store') }}" novalidate>
        @csrf

        <div class="grid gap-4 p-6 bg-white md:p-7 md:grid-cols-2">
          {{-- Code --}}
          <div class="grid gap-4 md:col-span-2 md:grid-cols-2">
            <div>
              <label class="label">RFR MinePro</label>
              <div class="flex gap-2">
                <select id="rfr_ref" class="input flex-1" required style="--tw-ring-color: {{ $ACCENT }}">
                  <option value="">— Pilih RFR MinePro —</option>
                  @foreach($rfrCompact as $rfr)
                    <option value="{{ $rfr['code'] }}">{{ $rfr['api_row_no'] ?? $loop->iteration }}. {{ $rfr['code'] }} — {{ $rfr['title'] }} · {{ $rfr['department'] }} · {{ $rfr['site_code'] }} · Qty {{ $rfr['qty_required'] }}</option>
                  @endforeach
                </select>
              </div>
              <p class="mt-1 text-xs text-emerald-700" id="rfr_status"></p>
              @if(empty($rfrVacancies))
                <p class="mt-1 text-xs text-amber-700" id="rfr_hint_empty">Data RFR belum tersedia untuk periode ini. Ubah StartDate/EndDate lalu klik Ambil RFR.</p>
              @else
                <p class="mt-1 text-xs text-slate-500" id="rfr_hint">{{ count($rfrVacancies) }} RFR ditemukan dari API — form terisi otomatis dari RFR pertama. Tempel RFRRefID / Position_Ref untuk memakai RFR lain.</p>
              @endif
              @if(!empty($rfrMeta))
                <p class="mt-1 text-[11px] text-slate-400 break-all">
                  MinePro: {{ $rfrMeta['url'] ?? '-' }} · StartDate {{ $rfrMeta['start_date'] ?? ($rfrStartDate ?? '-') }} · EndDate {{ $rfrMeta['end_date'] ?? ($rfrEndDate ?? '-') }} · raw {{ (int) ($rfrMeta['raw_count'] ?? 0) }} · tersedia {{ (int) ($rfrMeta['count'] ?? count($rfrVacancies ?? [])) }}{{ empty($rfrMeta['ok']) && !empty($rfrMeta['message']) ? ' · '.$rfrMeta['message'] : '' }}
                </p>
              @endif
            </div>
            <div>
            <label class="label">Code <span class="text-rose-600">*</span></label>
            <input class="input bg-slate-50 cursor-not-allowed" name="code" id="code" value="{{ old('code') }}" required maxlength="50"
               placeholder="Otomatis dari RFR (RFRRefID)" style="--tw-ring-color: {{ $ACCENT }}" readonly>
            @error('code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
          </div>

          {{-- Title --}}
          <div>
            <label class="label">Title <span class="text-rose-600">*</span></label>
            <input class="input bg-slate-50 cursor-not-allowed" name="title" value="{{ old('title') }}" required maxlength="200"
               placeholder="Otomatis dari RFR" style="--tw-ring-color: {{ $ACCENT }}" readonly>
            @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Division --}}
          <div>
            <label class="label">Division</label>
            @php $divisionOld = old('division'); @endphp
            <input class="input bg-slate-50 cursor-not-allowed" id="division_display" value="{{ old('division') }}" readonly>
            <input type="hidden" name="division" id="division" value="{{ old('division') }}">
            @error('division')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Level --}}
          <div>
            <label class="label">Level</label>
            @php $levelOld = old('level'); @endphp
            <input class="input bg-slate-50 cursor-not-allowed" id="level_display" value="{{ old('level') }}" readonly>
            <input type="hidden" name="level" id="level" value="{{ old('level') }}">
            @error('level')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Site --}}
          <div>
            <label class="label">Site <span class="text-rose-600">*</span></label>
            <select class="input hidden" name="site_id" id="site_id" style="--tw-ring-color: {{ $ACCENT }}">
              <option value="">— Pilih Site —</option>
              @forelse($sites as $s)
                <option value="{{ $s->id }}" data-code="{{ $s->code }}"
                  @selected(old('site_id') == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
              @empty
                <option value="" disabled>Tidak ada data site</option>
              @endforelse
            </select>
            <input class="input bg-slate-50 cursor-not-allowed" id="site_display" value="" readonly>
            <input type="hidden" name="site_code" id="site_code" value="{{ old('site_code') }}">
            <p class="mt-1 text-xs text-slate-500">Otomatis dari <code>LokasiKerja/ProjectID</code> MinePro.</p>
            @error('site_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            @error('site_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Employment Type --}}
          <div>
            <label class="label">Employment Type <span class="text-rose-600">*</span></label>
            @php $et = old('employment_type', 'fulltime'); @endphp
            <select class="input hidden" name="employment_type" required style="--tw-ring-color: {{ $ACCENT }}">
              <option value="fulltime" @selected($et === 'fulltime')>Fulltime</option>
              <option value="contract" @selected($et === 'contract')>Contract</option>
              <option value="intern"   @selected($et === 'intern')>Intern</option>
            </select>
            <input class="input bg-slate-50 cursor-not-allowed" value="Fulltime" readonly>
            @error('employment_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Company (opsional) + Company code (opsional) --}}
          <div class="grid gap-4 md:col-span-2 md:grid-cols-2">
            <div>
              <label class="label">Company (opsional)</label>
              <select class="input hidden" name="company_id" id="company_id" style="--tw-ring-color: {{ $ACCENT }}">
                <option value="">— Tidak ada company —</option>
                @forelse(($companies ?? []) as $c)
                      <option value="{{ data_get($c, 'id') }}" data-code="{{ data_get($c, 'code') }}"
                        @selected((string) old('company_id') === (string) data_get($c, 'id'))>{{ data_get($c, 'code') }} — {{ data_get($c, 'name') }}</option>
                @empty
                      <option value="" disabled>Tidak ada data company</option>
                @endforelse
              </select>
              <input class="input bg-slate-50 cursor-not-allowed" id="company_display" value="" readonly>
              @error('company_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
              <label class="label">Company Code (opsional)</label>
              <input class="input bg-slate-50 cursor-not-allowed" name="company_code" id="company_code" value="{{ old('company_code') }}"
                     maxlength="50" placeholder="Otomatis dari RFR" style="--tw-ring-color: {{ $ACCENT }}" readonly>
              <p class="mt-1 text-xs text-slate-500">
                Isi salah satu: <code>Company</code> (dropdown) <em>atau</em> <code>Company Code</code>.
              </p>
              @error('company_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
          </div>

          {{-- Status --}}
          <div>
            <label class="label">Status <span class="align-top text-[10px] px-1 rounded bg-slate-100 text-slate-700">admin</span></label>
            @php $st = old('status', 'open'); @endphp
            <select class="input hidden" name="status" style="--tw-ring-color: {{ $ACCENT }}">
              <option value="draft"  @selected($st === 'draft')>Draft</option>
              <option value="open"   @selected($st === 'open')>Open</option>
              <option value="closed" @selected($st === 'closed')>Closed</option>
            </select>
            <input class="input bg-slate-50 cursor-not-allowed" value="Open" readonly>
            @error('status')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Openings (disabled, disinkron dari Manpower) --}}
          <div>
            <label class="label">Openings</label>
            <input class="input" id="openings_display" type="number" min="0" value="{{ old('initial_openings', 0) }}" disabled
                   style="--tw-ring-color: {{ $ACCENT }}">
            <input type="hidden" name="initial_openings" id="initial_openings" value="{{ old('initial_openings', 0) }}">
            <p class="mt-1 text-xs text-slate-500">Kalau pilih RFR, nilai ini diambil dari <code>QtyRequired</code> lalu dibuatkan <em>Manpower Requirements</em> otomatis.</p>
          </div>

          {{-- Keywords (string, max:500) --}}
          <div class="md:col-span-2">
            <label class="label">Keywords (SEO internal)</label>
            <input class="input" name="keywords" id="keywords" maxlength="500"
                   value="{{ old('keywords') }}" placeholder="contoh: excavator, operator alat berat, tambang"
                   style="--tw-ring-color: {{ $ACCENT }}">
            <div class="flex items-center justify-between mt-1 text-xs text-slate-500">
              <span>Pisahkan dengan koma untuk memudahkan pencarian.</span>
              <span id="kw_count">0/500</span>
            </div>
            @error('keywords')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Skills (array/string; controller aman) --}}
          <div class="md:col-span-2">
            <label class="label">Skills</label>
            <textarea class="input min-h-[84px]" name="skills" id="skills"
                      placeholder="Ketik skill, pisahkan dengan koma atau Enter. Contoh: Excavator A40, SIM B2 Umum, Basic Safety"
                      style="--tw-ring-color: {{ $ACCENT }}">{{ old('skills') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Boleh diisi: <em>comma-separated</em> atau satu skill per baris. Sistem akan menormalkan sebagai array.</p>
            @error('skills')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
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
                      border-radius:.5rem; padding:.75rem; min-height:10rem; background:#fff;
                    }
                    trix-toolbar{ border:1px solid {{ $BORD }}; border-radius:.5rem; margin-bottom:.5rem; }
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
            @error('description')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>
        </div>
      </form>
    </div>

    {{-- Helpers & UX scripts --}}
    <script>
      (function(){
        const siteSel = document.getElementById('site_id');
        const siteCode = document.getElementById('site_code');
        const compSel = document.getElementById('company_id');
        const compCode = document.getElementById('company_code');
        const rfrInput = document.getElementById('rfr_ref');
        const rfrStatus = document.getElementById('rfr_status');
        const rfrList = @json($rfrCompact);
        const oldCode = @json(old('code', null));
        const code = document.getElementById('code');
        const title = document.querySelector('[name="title"]');
        const division = document.getElementById('division');
        const divisionDisplay = document.getElementById('division_display');
        const level = document.getElementById('level');
        const levelDisplay = document.getElementById('level_display');
        const siteDisplay = document.getElementById('site_display');
        const companyDisplay = document.getElementById('company_display');
        const descInput = document.getElementById('desc_input');
        const kw = document.getElementById('keywords');
        const skills = document.getElementById('skills');
        const openingsDisplay = document.getElementById('openings_display');
        const initialOpenings = document.getElementById('initial_openings');
        const form = document.getElementById('jobCreateForm');
        const kwCount = document.getElementById('kw_count');

        // Initialize counters
        if (kw) {
          const updateKw = () => kwCount && (kwCount.textContent = (kw.value?.length||0) + '/500');
          kw.addEventListener('input', updateKw);
          updateKw();
        }

        // Auto-populate site_code from selected site
        function syncSiteCode(){
          const opt = siteSel?.options[siteSel.selectedIndex];
          const code = opt?.getAttribute?.('data-code') || '';
          if (code) siteCode.value = code;
          else siteCode.value = '';
        }
        siteSel?.addEventListener('change', syncSiteCode);
        // Run once on load if old() exists
        syncSiteCode();

        function normalizeOptionValue(raw) {
          return (raw || '')
            .toString()
            .trim()
            .toLowerCase()
            .replace(/&/g, 'and')
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
        }

        function setSiteByCode(rawCode) {
          if (!siteSel || !rawCode) return;
          const target = rawCode.toString().trim().toLowerCase();
          if (siteCode) siteCode.value = rawCode.toString().trim();
          if (siteDisplay) siteDisplay.value = rawCode.toString().trim();
          const match = Array.from(siteSel.options).find((opt) => {
            return (opt.dataset.code || '').toLowerCase() === target
              || opt.textContent.toLowerCase().includes(target);
          });
          if (match) {
            siteSel.value = match.value;
            syncSiteCode();
            if (siteDisplay) siteDisplay.value = match.textContent.trim();
          } else {
            const existingApiOption = siteSel.querySelector('option[data-api-site="1"]');
            if (existingApiOption) existingApiOption.remove();
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
          if (editor?.editor) {
            editor.editor.loadHTML(html);
          }
        }

        function applyRfr(rfr) {
          if (!rfr) return;
          if (code) code.value = rfr.code || '';
          if (title && rfr.title) title.value = rfr.title;
          if (division) division.value = rfr.department || '';
          if (divisionDisplay) divisionDisplay.value = rfr.department || '';
          if (level) level.value = normalizeOptionValue(rfr.level);
          if (levelDisplay) levelDisplay.value = rfr.level || '';
          setSiteByCode(rfr.site_code);
          if (compCode && rfr.company_code) {
            if (compSel) compSel.value = '';
            compCode.value = rfr.company_code;
            if (companyDisplay) companyDisplay.value = rfr.company_code;
            toggleCompanyInputs();
          }
          setTrixDescription(rfr.description);
          if (initialOpenings && openingsDisplay) {
            const qty = Math.max(0, parseInt(rfr.qty_required || '0', 10) || 0);
            initialOpenings.value = qty;
            openingsDisplay.value = qty;
          }

          const keywordParts = [
            rfr.title,
            rfr.department,
            rfr.site_code,
            rfr.education_level,
            rfr.discipline,
            rfr.program_study,
            rfr.candidate_type,
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
          if (skills && skillParts.length) {
            skills.value = skillParts.join(', ');
          }

          if (rfrStatus) {
            const ref = rfr.position_ref ? ` · Position_Ref #${rfr.position_ref}` : '';
            rfrStatus.textContent = `Dipakai: ${rfr.code || '-'}${ref} — ${rfr.title || 'Tanpa posisi'}`;
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
            rfrStatus.textContent = `Tidak ada RFR dengan kode/ref “${rfrInput.value.trim()}”. Cek rentang tanggal atau isi manual.`;
            rfrStatus.classList.remove('text-emerald-700');
            rfrStatus.classList.add('text-rose-600');
          }
        }

        rfrInput?.addEventListener('change', applyRfrFromInput);
        rfrInput?.addEventListener('keydown', function(e){
          if (e.key === 'Enter') { e.preventDefault(); applyRfrFromInput(); }
        });
        rfrInput?.addEventListener('blur', applyRfrFromInput);

        // Auto-fill dari RFR pertama saat halaman baru (belum ada old())
        if (rfrList.length && !oldCode) {
          applyRfr(rfrList[0]);
        }

        // Mutual exclusion company_id <-> company_code (Rule: prohibits)
        function toggleCompanyInputs(){
          const hasDropdown = !!compSel?.value;
          const hasManual   = !!compCode?.value.trim();

          if (hasDropdown) {
            compCode.value = '';
            compCode.classList.add('bg-slate-50','cursor-not-allowed');
          } else {
            compCode.classList.remove('bg-slate-50','cursor-not-allowed');
          }

          if (hasManual) {
            compSel.value = '';
          }
        }
        compSel?.addEventListener('change', toggleCompanyInputs);
        compCode?.addEventListener('input', toggleCompanyInputs);
        toggleCompanyInputs();

        // Normalize skills on submit: accept comma or newline → JSON-ish array string or plain
        form?.addEventListener('submit', function(e){
          // Best effort normalize skills → "a, b, c" or lines to "a,b,c"
          if (skills && skills.value.trim().length){
            let raw = skills.value
              .split(/[\n,]/g)
              .map(s => s.trim())
              .filter(Boolean);
            // Controller kamu aman untuk string/array; kita kirim sebagai comma-joined for simplicity
            skills.value = raw.join(', ');
          }
        }, {passive:true});
      })();
    </script>
@endsection
