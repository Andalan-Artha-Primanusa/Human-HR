{{-- resources/views/admin/dashboard/manpower.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Manpower Dashboard' ])

@section('content')
  {{-- ===== Header ===== --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm/50 mb-6 overflow-hidden">
    <div class="h-2">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width:90%"></div>
        <div class="h-full bg-red-500"  style="width:10%"></div>
      </div>
    </div>
    <div class="p-6 md:p-8">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Manpower Dashboard</h1>
          <p class="mt-1 text-sm text-slate-600">Ringkasan lowongan, kandidat aktif, headcount, dan pipeline.</p>
        </div>
        <a href="{{ route('admin.jobs.index') }}"
           class="hidden sm:inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 active:bg-slate-100">
          Kelola Jobs
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>
  </div>

  {{-- ===== KPI Cards ===== --}}
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
    @php
      $kpis = [
        ['label'=>'Open Jobs','value'=>number_format($openJobs),'icon'=>'M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z','bg'=>'bg-blue-50','fg'=>'text-blue-600'],
        ['label'=>'Active Applicants','value'=>number_format($activeApps),'icon'=>'M15 19a4 4 0 1 0-6 0m9-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z','bg'=>'bg-emerald-50','fg'=>'text-emerald-600'],
        ['label'=>'Headcount Budget','value'=>number_format($budget),'icon'=>'M3 19.5h18M6 17V9m6 8V5m6 12v-6','bg'=>'bg-amber-50','fg'=>'text-amber-600'],
        ['label'=>'Fulfillment','value'=>number_format($fulfillment,0)."%",'icon'=>'m9 12 2 2 4-4m5 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z','bg'=>'bg-purple-50','fg'=>'text-purple-600'],
      ];
    @endphp

    @foreach ($kpis as $k)
      <div class="card transition-shadow hover:shadow-md">
        <div class="card-body">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-slate-600 text-sm">{{ $k['label'] }}</div>
              <div class="text-2xl font-semibold tracking-tight text-slate-900 mt-1">{{ $k['value'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $k['bg'] }} {{ $k['fg'] }} grid place-content-center ring-1 ring-inset ring-black/5">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- ===== Pipeline by Stage ===== --}}
  <div class="card">
    <div class="card-body">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-900">Pipeline by Stage</h2>
        <div class="hidden md:flex items-center gap-3 text-xs text-slate-500">
          <span class="inline-flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-sm bg-blue-500/80"></span> Applications
          </span>
        </div>
      </div>

      @php $hasData = is_array($byStage ?? null) && count($byStage ?? []) > 0; @endphp

      @if($hasData)
        <div class="relative h-[260px]">
          <canvas id="byStageChart" class="!h-[260px]"></canvas>
        </div>
      @else
        <div class="rounded-xl border border-dashed border-slate-300 p-10 text-center bg-white/50">
          <div class="text-slate-700 font-medium">Belum ada data pipeline.</div>
          <div class="text-slate-500 text-sm mt-1">Tambahkan aplikasi atau buka lowongan untuk melihat grafik.</div>
        </div>
      @endif
    </div>
  </div>

  {{-- ===== Headcount Estimator + Quick Edit ===== --}}
  <div class="grid md:grid-cols-3 gap-6 mt-6">
    <div class="card md:col-span-2">
      <div class="card-body">
        <div class="flex items-start justify-between mb-4 gap-4">
          <div>
            <h2 class="font-semibold text-slate-900">Headcount Estimator</h2>
            <p class="text-xs text-slate-500 mt-1">
              Preview cepat: kirim <code>assets_count</code> & <code>ratio_per_asset</code> ke endpoint preview.
              <span class="block mt-0.5">Nama aset diatur saat simpan di halaman <em>Atur Manpower</em>.</span>
            </p>
          </div>
          <div class="hidden md:flex items-center gap-2 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-50 ring-1 ring-inset ring-black/5">
              <span class="w-2 h-2 rounded-full bg-teal-500"></span> real-time preview
            </span>
          </div>
        </div>

        <form id="estimatorForm" class="grid grid-cols-1 md:grid-cols-5 gap-4">
          {{-- Jumlah Aset --}}
          <div>
            <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Jumlah Aset</label>
            <input type="number" min="0" step="1" name="assets_count" value="0"
                   class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition"/>
          </div>

          {{-- Rasio --}}
          <div>
            <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Rasio per Aset</label>
            <input type="number" step="0.01" name="ratio_per_asset" value="2.50"
                   class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition"/>
            <div class="mt-2 flex items-center gap-2">
              <button type="button" data-ratio="2.50"
                      class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 active:bg-slate-100 text-xs shadow-sm">2.50</button>
              <button type="button" data-ratio="2.60"
                      class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 active:bg-slate-100 text-xs shadow-sm">2.60</button>
            </div>
          </div>

          {{-- Nama Aset --}}
          <div>
            <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Nama Aset (opsional)</label>
            <input type="text" name="asset_name" placeholder="mis. Dump Truck HD785 / Excavator PC200"
                   class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition"/>
          </div>

          {{-- Pilih Job --}}
          <div>
            <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Pilih Job</label>
            <div class="relative mb-2">
              <select id="jumpJob"
                      class="w-full appearance-none rounded-xl border-slate-300 bg-white px-3 py-2.5 pr-8 text-slate-900 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition">
                <option value="">— pilih job —</option>
                @foreach(($jobsForManpower ?? []) as $j)
                  <option value="{{ $j->id }}">{{ $j->code ?? 'JOB' }} — {{ $j->title }}</option>
                @endforeach
              </select>
              <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
              </svg>
            </div>

            {{-- Cari cepat (datalist) --}}
            @php
              $jobOptions = ($jobsForManpower ?? collect())->map(fn($x) => [
                'id' => $x->id,
                'label' => trim(($x->code ?? 'JOB').' — '.$x->title),
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
              <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
              </svg>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Pilih dari dropdown atau ketik lalu tekan Enter.</p>
          </div>

          {{-- Actions --}}
          <div class="flex items-end gap-2">
            <button id="btnEstimate" type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 text-white hover:bg-teal-700 active:bg-teal-800 shadow-sm transition">
              Hitung
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h6m0 0v6m0-6-8 8-4-4-6 6"/>
              </svg>
            </button>

            {{-- === Tombol Atur Manpower (baru, rounded, stateful) === --}}
            <a id="btnJump" href="#"
               class="group inline-flex items-center gap-2 rounded-full px-4 py-2.5
                      bg-slate-100 text-slate-400 border border-slate-200 shadow-sm
                      pointer-events-none opacity-60
                      transition-all
                      focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
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

        {{-- Preview hasil --}}
        <div id="estimatorResult" class="mt-5 hidden">
          <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 md:p-5 flex items-center justify-between gap-4">
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
            <div class="hidden md:block text-right text-xs text-slate-500">
              <div class="rounded-lg bg-white/60 ring-1 ring-inset ring-black/5 px-3 py-2">
                <div>Rumus: <code>ceil(assets × ratio)</code></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- ===== Quick Edit ===== --}}
    <div class="card">
      <div class="card-body">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-semibold text-slate-900">Quick Edit</h2>
          <span class="text-xs text-slate-500">6 terbaru</span>
        </div>

        @php $quick = ($jobsForManpower ?? collect())->take(6); @endphp

        @if ($quick->isEmpty())
          <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center bg-white/50 text-sm text-slate-600">
            Belum ada job.
          </div>
        @else
          <ul class="space-y-2">
            @foreach ($quick as $q)
              <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3.5 py-2.5 bg-white hover:bg-slate-50">
                <div class="min-w-0">
                  <div class="font-medium text-slate-900 truncate">{{ $q->title }}</div>
                  <div class="text-xs text-slate-500">{{ $q->code ?? 'JOB' }}</div>
                </div>
                <a href="{{ route('admin.manpower.edit', $q->id) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-white text-xs hover:bg-blue-700 active:bg-blue-800">
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
  </div>

  {{-- ===== Scripts: Chart.js ===== --}}
  @if($hasData)
    <script>
      (function loadChartJS(cb){
        if (window.Chart) return cb();
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js';
        s.onload = cb; document.head.appendChild(s);
      })(function initChart(){
        const stageData = @json($byStage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        const labels = Object.keys(stageData);
        const values = Object.values(stageData);

        const ctx = document.getElementById('byStageChart');
        if(!ctx) return;

        const palette = [
          'rgba(59,130,246,0.75)','rgba(99,102,241,0.75)','rgba(245,158,11,0.75)',
          'rgba(16,185,129,0.75)','rgba(168,85,247,0.75)','rgba(236,72,153,0.75)',
          'rgba(20,184,166,0.75)','rgba(100,116,139,0.75)',
        ];
        const bg = labels.map((_, i) => palette[i % palette.length]);

        new Chart(ctx, {
          type: 'bar',
          data: { labels, datasets: [{ label: 'Applications', data: values, backgroundColor: bg, borderRadius: 8, borderSkipped: false }] },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: (i) => ` ${i.formattedValue} aplikasi` } } },
            scales: {
              x: { grid: { display: false }, ticks: { color: '#475569' } },
              y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.12)' }, ticks: { precision: 0, color: '#64748b' } }
            }
          }
        });
      });
    </script>
  @endif

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

      // --- class set untuk tombol (enabled/disabled) ---
      const BTN_ENABLED =
        "inline-flex items-center gap-2 rounded-full px-4 py-2.5 " +
        "bg-teal-600 text-white border border-teal-600 shadow-sm " +
        "hover:bg-teal-700 active:bg-teal-800 focus:outline-none " +
        "focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-500 " +
        "pointer-events-auto opacity-100";
      const BTN_DISABLED =
        "inline-flex items-center gap-2 rounded-full px-4 py-2.5 " +
        "bg-slate-100 text-slate-400 border border-slate-200 shadow-sm " +
        "pointer-events-none opacity-60 focus:outline-none";

      // mapping label -> id untuk datalist
      const JOBS_MAP = new Map(
        @json($jobOptions ?? [])
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
          input?.focus();
          input?.select();
        });
      });

      // dropdown -> enable tombol
      jumpSel?.addEventListener('change', () => setJumpTarget(jumpSel.value || null));

      // datalist input -> cari id
      jobLookup?.addEventListener('change', () => {
        const id = JOBS_MAP.get(jobLookup.value.trim());
        setJumpTarget(id || null);
      });

      // enter di jobLookup -> langsung lompat kalau ada id
      jobLookup?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          const id = JOBS_MAP.get(jobLookup.value.trim());
          if (id) {
            window.location.href = @json(route('admin.manpower.edit', ':id')).replace(':id', id);
          }
        }
      });

      // estimator preview
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
