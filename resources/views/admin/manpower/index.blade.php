{{-- resources/views/admin/manpower/index.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Atur Manpower'])

@section('content')
    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: #e5e7eb;">
        <div class="relative h-20 overflow-hidden sm:h-24 rounded-t-2xl">
          <div class="absolute inset-0 rounded-t-2xl" style="background: linear-gradient(90deg, #a77d52, #8b5e3c);"></div>
          <div class="absolute inset-y-0 right-0 w-24 rounded-tr-2xl sm:w-36" style="background: linear-gradient(90deg, #8b5e3c, #a77d52);"></div>

          <div class="relative flex items-center h-full px-5 text-white md:px-6">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Atur Manpower</h1>
              <p class="text-xs sm:text-sm text-white/90">Estimator kebutuhan headcount dan pintasan edit per job.</p>
            </div>
          </div>
        </div>
      </section>

      {{-- ===== Headcount Estimator ===== --}}
      <div class="card">
        <div class="card-body">
          <div class="flex items-start justify-between gap-4 mb-4">
            <div>
              <h2 class="font-semibold text-slate-900">Headcount Estimator</h2>
              <p class="mt-1 text-xs text-slate-500">
                Preview cepat: kirim <code>assets_count</code> & <code>ratio_per_asset</code> ke endpoint preview.
                <span class="block mt-0.5">Nama aset diatur saat simpan di halaman <em>Atur Manpower</em>.</span>
              </p>
            </div>
            <div class="items-center hidden gap-2 text-xs md:flex text-slate-500">
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-50 ring-1 ring-inset ring-black/5">
                <span class="w-2 h-2 bg-teal-500 rounded-full"></span> real-time preview
              </span>
            </div>
          </div>

          <form id="estimatorForm" class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div>
              <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Jumlah Aset</label>
              <input type="number" min="0" step="1" name="assets_count" value="0"
                     class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition"/>
            </div>

            <div>
              <label class="block text:[11px] uppercase tracking-wide text-slate-600 mb-1">Rasio per Aset</label>
              <input type="number" step="0.01" name="ratio_per_asset" value="2.50"
                     class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition"/>
              <div class="flex items-center gap-2 mt-2">
                <button type="button" data-ratio="2.50" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 active:bg-slate-100 text-xs shadow-sm">2.50</button>
                <button type="button" data-ratio="2.60" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 active:bg-slate-100 text-xs shadow-sm">2.60</button>
              </div>
            </div>

            <div>
              <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Nama Aset (opsional)</label>
              <input type="text" name="asset_name" placeholder="mis. Dump Truck HD785 / Excavator PC200"
                     class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition"/>
            </div>

            <div>
              <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Pilih Job</label>
              <div class="relative mb-2">
                <select id="jumpJob"
                        class="w-full appearance-none rounded-xl border-slate-300 bg-white px-3 py-2.5 pr-8 text-slate-900 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition">
                  <option value="">— pilih job —</option>
                  @foreach(($jobsForManpower ?? []) as $j)
                    <option value="{{ data_get($j, 'id') }}">{{ data_get($j, 'code', 'JOB') }} — {{ data_get($j, 'title') }}</option>
                  @endforeach
                </select>
                <svg class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                </svg>
              </div>

              @php
                $jobOptions = ($jobsForManpower ?? collect())->map(fn($x) => [
                    'id' => $x->id,
                    'label' => trim(($x->code ?? 'JOB') . ' — ' . $x->title),
                ])->values();
              @endphp
              <div class="relative">
                <input id="jobLookup" list="jobsList" placeholder="Cari kode / judul…"
                       class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 pr-10 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition"/>
                <datalist id="jobsList">
                  @foreach($jobOptions as $o)
                    <option value="{{ $o['label'] }}"></option>
                  @endforeach
                </datalist>
                <svg class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                </svg>
              </div>
              <p class="text-[11px] text-slate-500 mt-1">Pilih dari dropdown atau ketik lalu tekan Enter.</p>
            </div>

            <div class="flex items-end gap-2">
              <button id="btnEstimate" type="submit"
                      class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 text-white hover:bg-teal-700 active:bg-teal-800 shadow-sm transition">
                Hitung
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h6m0 0v6m0-6-8 8-4-4-6 6"/>
                </svg>
              </button>

              <a id="btnJump" href="#"
                 class="group inline-flex items-center gap-2 rounded-full px-4 py-2.5
                        bg-slate-100 text-slate-400 border border-slate-200 shadow-sm
                        pointer-events-none opacity-60 transition-all
                        focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
                </svg>
                <span class="font-medium">Atur Manpower</span>
                <svg class="h-4 w-4 translate-x-0 opacity-0 transition-all group-hover:translate-x-0.5 group-hover:opacity-100"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 8 7-8 7"/>
                </svg>
              </a>
            </div>
          </form>

          <div id="estimatorResult" class="hidden mt-5">
            <div class="flex items-center justify-between gap-4 p-4 border rounded-2xl bg-slate-50 border-slate-200 md:p-5">
              <div>
                <div class="text-sm text-slate-700">
                  Perkiraan <span class="font-medium">Budget Headcount</span> (preview)
                  <span class="text-slate-500" id="estimatorAssetName"></span>:
                </div>
                <div class="mt-1 text-3xl font-semibold tracking-tight text-slate-900" id="estimatorBudget">0</div>
                <div class="mt-2 text-xs text-slate-500">
                  Nilai ini belum tersimpan. Simpan & sinkron ke <code>jobs.openings</code> di halaman <em>Atur Manpower</em>.
                </div>
              </div>
              <div class="hidden text-xs text-right md:block text-slate-500">
                <div class="px-3 py-2 rounded-lg bg-white/60 ring-1 ring-inset ring-black/5">
                  <div>Rumus: <code>ceil(assets × ratio)</code></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ===== Quick Edit (6 terbaru) ===== --}}
      <div class="mt-6 card">
        <div class="card-body">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-slate-900">Quick Edit</h2>
            <span class="text-xs text-slate-500">6 terbaru</span>
          </div>

          @php $quick = ($jobsForManpower ?? collect())->take(6); @endphp

          @if ($quick->isEmpty())
            <div class="p-6 text-sm text-center border border-dashed rounded-lg border-slate-300 bg-white/50 text-slate-600">
              Belum ada job.
            </div>
          @else
            <ul class="space-y-2">
              @foreach ($quick as $q)
                <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3.5 py-2.5 bg-white hover:bg-slate-50">
                  <div class="min-w-0">
                    <div class="font-medium truncate text-slate-900">{{ $q->title }}</div>
                    <div class="text-xs text-slate-500">{{ $q->code ?? 'JOB' }}</div>
                  </div>
                  <a href="{{ route('admin.manpower.edit', $q->id) }}"
                     class="inline-flex items-center gap-1.5 rounded-lg bg-[#a77d52] px-3 py-1.5 text-white text-xs hover:bg-[#a77d52] active:bg-blue-800">
                    Atur Manpower
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
                    </svg>
                  </a>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>

      {{-- ===== Scripts: Estimator + Jump ===== --}}
      <script>
        (function(){
          const form     = document.getElementById('estimatorForm');
          const result   = document.getElementById('estimatorResult');
          const out      = document.getElementById('estimatorBudget');
          const outName  = document.getElementById('estimatorAssetName');
          const ratioBtns= form?.querySelectorAll('button[data-ratio]') || [];
          const route    = @json(route('admin.manpower.preview'));
          const jumpSel  = document.getElementById('jumpJob');
          const jumpBtn  = document.getElementById('btnJump');
          const jobLookup= document.getElementById('jobLookup');

          const BTN_ENABLED =
            "inline-flex items-center gap-2 rounded-full px-4 py-2.5 bg-teal-600 text-white border border-teal-600 shadow-sm hover:bg-teal-700 active:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-500 pointer-events-auto opacity-100";
          const BTN_DISABLED =
            "inline-flex items-center gap-2 rounded-full px-4 py-2.5 bg-slate-100 text-slate-400 border border-slate-200 shadow-sm pointer-events-none opacity-60 focus:outline-none";

          const JOBS_MAP = new Map(
            @json(($jobsForManpower ?? collect())->map(fn($x) => [
                'id' => $x->id,
                'label' => trim(($x->code ?? 'JOB') . ' — ' . $x->title),
            ])->values())
              .map(j => [j.label, j.id])
          );

          function setJumpTarget(id) {
            if (!jumpBtn) return;
            if (id) {
              jumpBtn.href = @json(route('admin.manpower.edit', ':id')).replace(':id', id);
              jumpBtn.className = "group " + BTN_ENABLED;
            } else {
              jumpBtn.href = "#";
              jumpBtn.className = "group " + BTN_DISABLED;
            }
          }

          ratioBtns.forEach(b => {
            b.addEventListener('click', () => {
              const input = form.querySelector('input[name="ratio_per_asset"]');
              if (input) input.value = b.dataset.ratio;
              input?.focus(); input?.select();
            });
          });

          jumpSel?.addEventListener('change', () => setJumpTarget(jumpSel.value || null));

          jobLookup?.addEventListener('change', () => {
            const id = JOBS_MAP.get(jobLookup.value.trim());
            setJumpTarget(id || null);
          });

          jobLookup?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
              const id = JOBS_MAP.get(jobLookup.value.trim());
              if (id) {
                window.location.href = @json(route('admin.manpower.edit', ':id')).replace(':id', id);
              }
            }
          });

          form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const assets = parseInt(form.assets_count.value || '0', 10);
            const ratio  = parseFloat(form.ratio_per_asset.value || '0');
            const name   = (form.asset_name?.value || '').trim();

            try {
              const res = await fetch(route, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ assets_count: isNaN(assets) ? 0 : assets, ratio_per_asset: isNaN(ratio) ? 0 : ratio })
              });
              if (!res.ok) throw new Error('Gagal menghitung.');
              const data = await res.json();
              out.textContent = (data?.result?.budget_headcount ?? 0).toLocaleString('id-ID');
              outName.textContent = name ? `untuk aset “${name}”` : '';
              result.classList.remove('hidden');
            } catch (err) {
              alert(err.message || 'Terjadi kesalahan.');
            }
          });
        })();
      </script>
@endsection
