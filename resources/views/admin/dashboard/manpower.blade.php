@extends('layouts.app', ['title' => 'Admin · Manpower Dashboard'])

@php
    $primary = '#a77d52';
    $secondary = '#8b9f6f';
    $accent = '#2f6f6d';
    $danger = '#b45309';

    $sourceLabels = $sourceLabels ?? [];
    $educationLabels = $educationLabels ?? [];
    $openJobCards = collect($openJobCards ?? []);
    $levelStats = collect($levelStats ?? []);
    $slaLevelStats = $levelStats->filter(fn ($row) => (($row['hired'] ?? 0) > 0) && (($row['avg_sla_days'] ?? 0) > 0));
    $hasSlaChartData = $slaLevelStats->isNotEmpty();
    $failureRows = collect($failureRows ?? []);
    $olRejectionReasons = collect($olRejectionReasons ?? []);
    $sites = collect($sites ?? []);
    $selectedSiteId = (string) ($selectedSiteId ?? '');
    $selectedSite = $selectedSiteId !== '' ? $sites->firstWhere('id', $selectedSiteId) : null;
    $mppRows = collect($mppRows ?? []);
    $mppTotals = $mppTotals ?? [];
    $periodStart = $periodStart ?? now()->startOfMonth()->toDateString();
    $periodEnd = $periodEnd ?? now()->toDateString();
    $interviewRows = collect($interviewRows ?? []);
    $interviewTotals = $interviewTotals ?? ['total' => 0, 'online' => 0, 'onsite' => 0];
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  <x-admin.page-header
    eyebrow="MANPOWER INTELLIGENCE"
    title="Manpower Dashboard"
    description="Compact presentation view for SLA per level, applicant source, POH, pendidikan, gender, dan stage failure.">
    <div class="page-header__meta">
      <p class="label">Last Updated</p>
      <p class="value">{{ ($generatedAt ?? now())->format('d M Y H:i') }}</p>
    </div>
  </x-admin.page-header>

  <section class="p-4 bg-white border shadow-sm rounded-2xl">
    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div>
        <h2 class="text-sm font-semibold text-slate-900">Filter Dashboard</h2>
        <p class="mt-1 text-xs text-slate-500">Pilih site dan periode untuk melihat manpower, progress stage, interview, dan grafik khusus data tersebut.</p>
      </div>
      <div class="flex flex-col gap-2 lg:flex-row lg:items-center">
        <select name="site_id" class="min-w-[260px] rounded-xl border-slate-200 text-sm focus:border-[#a77d52] focus:ring-[#a77d52]">
          <option value="">Semua Site</option>
          @foreach($sites as $site)
            <option value="{{ $site->id }}" @selected($selectedSiteId === $site->id)>
              {{ $site->code }} - {{ $site->name }}
            </option>
          @endforeach
        </select>
        <input type="date" name="start_date" value="{{ $periodStart }}" class="rounded-xl border-slate-200 text-sm focus:border-[#a77d52] focus:ring-[#a77d52]">
        <input type="date" name="end_date" value="{{ $periodEnd }}" class="rounded-xl border-slate-200 text-sm focus:border-[#a77d52] focus:ring-[#a77d52]">
        <button class="rounded-xl bg-[#a77d52] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">Terapkan</button>
        <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
      </div>
    </form>
    <div class="mt-3 flex flex-wrap gap-2">
      <span class="inline-flex rounded-full bg-[#f8f5f1] px-3 py-1 text-xs font-semibold text-[#8b5e3c] ring-1 ring-inset ring-[#ead8c5]">
        Tampilan: {{ $selectedSite ? (($selectedSite->code ?? '') . ' - ' . ($selectedSite->name ?? '')) : 'Semua Site' }}
      </span>
      <span class="inline-flex rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
        Periode: {{ $periodStart }} sampai {{ $periodEnd }}
      </span>
    </div>
  </section>

  @php
    $cards = [
      ['label' => 'Open Jobs', 'value' => $openJobs ?? 0, 'hint' => 'Posisi aktif', 'icon' => '💼'],
      ['label' => 'Total Applicants', 'value' => $totalApplicants ?? 0, 'hint' => 'Semua lamaran', 'icon' => '👥'],
      ['label' => 'Applicants / Open Job', 'value' => $openJobApplicants ?? 0, 'hint' => 'Akumulasi pelamar per job', 'icon' => '📊'],
      ['label' => 'Time to Fill', 'value' => ($timeToFillDays ?? 0) . ' hari', 'hint' => 'Open job → OL pertama dikirim', 'icon' => '⏱️'],
      ['label' => 'OL Diterima', 'value' => (int) ($acceptedOlCount ?? 0), 'hint' => 'Jumlah OL accepted', 'icon' => '✅'],
      ['label' => 'OL Ditolak', 'value' => (int) ($declinedOlCount ?? 0), 'hint' => 'Jumlah OL declined', 'icon' => '✕'],
      ['label' => 'Fulfillment', 'value' => ($fulfillment ?? 0) . '%', 'hint' => 'Sourcing + Onsite jadi karyawan', 'icon' => '⚡'],
      ['label' => 'SLA Success Rate', 'value' => ($acceptanceRate ?? 0) . '%', 'hint' => 'Terima OL / total applicants', 'icon' => '✅'],
    ];
  @endphp

  <section class="grid grid-cols-2 gap-3 xl:grid-cols-8">
    @foreach($cards as $card)
      <div class="p-4 transition bg-white border shadow-sm rounded-2xl hover:shadow-md">
        <div class="flex items-center justify-between gap-3">
          <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $card['label'] }}</p>
          <span class="text-lg">{{ $card['icon'] }}</span>
        </div>
        <p class="mt-2 text-xl font-bold text-[#a77d52]">{{ $card['value'] }}</p>
        <p class="mt-1 text-[11px] text-slate-500">{{ $card['hint'] }}</p>
      </div>
    @endforeach
  </section>

  <section class="grid grid-cols-1 gap-3 md:grid-cols-3">
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">Age Insight</p>
      <div class="grid grid-cols-3 gap-2 mt-3 text-center">
        <div class="p-3 rounded-xl bg-slate-50">
          <div class="text-xs text-slate-500">Avg</div>
          <div class="text-lg font-bold text-slate-900">{{ number_format((float) ($avgAge ?? 0), 1) }}</div>
        </div>
        <div class="p-3 rounded-xl bg-slate-50">
          <div class="text-xs text-slate-500">Min</div>
          <div class="text-lg font-bold text-slate-900">{{ (int) ($minAge ?? 0) }}</div>
        </div>
        <div class="p-3 rounded-xl bg-slate-50">
          <div class="text-xs text-slate-500">Max</div>
          <div class="text-lg font-bold text-slate-900">{{ (int) ($maxAge ?? 0) }}</div>
        </div>
      </div>
    </div>

    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">Top Failed Stage</p>
      <div class="flex items-end justify-between gap-4 mt-3">
        <div>
          <div class="text-sm text-slate-500">Stage</div>
          <div class="text-base font-bold text-[#a77d52]">{{ $failedStageName ?? '-' }}</div>
        </div>
        <div class="text-right">
          <div class="text-sm text-slate-500">Total Gagal</div>
          <div class="text-2xl font-bold text-[#b45309]">{{ (int) ($failedStageCount ?? 0) }}</div>
        </div>
      </div>
    </div>

    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">Overall Hiring</p>
      <div class="flex items-end justify-between gap-4 mt-3">
        <div>
          <div class="text-sm text-slate-500">Filled</div>
          <div class="text-base font-bold text-slate-900">{{ (int) ($filled ?? 0) }}</div>
        </div>
        <div class="text-right">
          <div class="text-sm text-slate-500">Budget Openings</div>
          <div class="text-2xl font-bold text-[#8b9f6f]">{{ (int) ($budget ?? 0) }}</div>
        </div>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">SLA per Level</h2>
        <span class="text-[11px] text-slate-400">Open job → Terima OL</span>
      </div>
      <div class="h-[200px]">
        <canvas id="slaLevelChart" class="w-full h-full"></canvas>
      </div>
      @unless($hasSlaChartData)
        <p class="mt-2 text-[11px] text-slate-400">Belum ada data SLA yang cukup untuk ditampilkan.</p>
      @endunless
    </div>

    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Applicant Source</h2>
        <span class="text-[11px] text-slate-400">Dari mana kandidat datang</span>
      </div>
      <div class="h-[200px]"><canvas id="sourceChart" class="w-full h-full"></canvas></div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Education Background</h2>
        <span class="text-[11px] text-slate-400">Pendidikan terakhir</span>
      </div>
      <div class="h-[200px]"><canvas id="educationChart" class="w-full h-full"></canvas></div>
    </div>

    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Gender Breakdown</h2>
        <span class="text-[11px] text-slate-400">Candidate gender</span>
      </div>
      <div class="h-[200px]"><canvas id="genderChart" class="w-full h-full"></canvas></div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Stage Failure</h2>
        <span class="text-[11px] text-slate-400">Failed / no-show stage</span>
      </div>
      <div class="h-[200px]"><canvas id="failureStageChart" class="w-full h-full"></canvas></div>
    </div>

    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Application Trend</h2>
        <span class="text-[11px] text-slate-400">Monthly intake</span>
      </div>
      <div class="h-[200px]"><canvas id="trendChart" class="w-full h-full"></canvas></div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h2 class="text-sm font-semibold text-slate-900">OL Summary</h2>
          <p class="text-[11px] text-slate-500">Ringkasan jumlah OL diterima dan ditolak.</p>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
          <div class="text-xs uppercase tracking-[0.18em] text-emerald-700">Accepted</div>
          <div class="mt-2 text-3xl font-bold text-emerald-700">{{ (int) ($acceptedOlCount ?? 0) }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100">
          <div class="text-xs uppercase tracking-[0.18em] text-rose-700">Declined</div>
          <div class="mt-2 text-3xl font-bold text-rose-700">{{ (int) ($declinedOlCount ?? 0) }}</div>
        </div>
      </div>
    </div>

    <div class="p-4 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h2 class="text-sm font-semibold text-slate-900">OL Rejection Reasons</h2>
          <p class="text-[11px] text-slate-500">Alasan penolakan yang paling sering muncul.</p>
        </div>
      </div>
      <div class="space-y-2">
        @forelse($olRejectionReasons as $item)
          <div class="flex items-start justify-between gap-4 p-3 rounded-xl bg-slate-50">
            <div class="text-sm text-slate-700 leading-snug">{{ $item['reason'] }}</div>
            <div class="shrink-0 text-sm font-bold text-[#a77d52]">{{ $item['total'] }}</div>
          </div>
        @empty
          <div class="p-4 text-sm text-slate-500 rounded-xl bg-slate-50">Belum ada OL yang ditolak.</div>
        @endforelse
      </div>
    </div>
  </section>

  <section class="p-4 bg-white border shadow-sm rounded-2xl">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 class="text-sm font-semibold text-slate-900">Open Jobs & Applicant Count</h2>
        <p class="text-[11px] text-slate-500">Per open job, terlihat berapa pelamar, terima OL, dan hiring per level.</p>
      </div>
      <span class="text-[11px] text-slate-400">{{ $openJobCards->count() }} jobs</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-xs uppercase bg-slate-50 text-slate-500">
          <tr>
            <th class="px-3 py-2 text-left">Job</th>
            <th class="px-3 py-2 text-left">Level</th>
            <th class="px-3 py-2 text-right">Openings</th>
            <th class="px-3 py-2 text-right">Applicants</th>
            <th class="px-3 py-2 text-right">Terima OL</th>
            <th class="px-3 py-2 text-right">Hired</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($openJobCards as $job)
            <tr>
              <td class="px-3 py-2 font-medium text-slate-800">{{ $job['title'] }}</td>
              <td class="px-3 py-2 text-slate-600">{{ $job['level_label'] }}</td>
              <td class="px-3 py-2 text-right text-slate-700">{{ $job['openings'] }}</td>
              <td class="px-3 py-2 text-right text-slate-700">{{ $job['applicants'] }}</td>
              <td class="px-3 py-2 text-right text-slate-700">{{ $job['accepted_ol'] }}</td>
              <td class="px-3 py-2 text-right text-slate-700">{{ $job['hired'] }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-3 py-6 text-center text-slate-500">Belum ada open job untuk ditampilkan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <section class="p-4 bg-white border shadow-sm rounded-2xl">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 class="text-sm font-semibold text-slate-900">Level Performance Summary</h2>
        <p class="text-[11px] text-slate-500">SLA dari open job sampai hired, applicant count, hired, dan success rate per level.</p>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-xs uppercase bg-slate-50 text-slate-500">
          <tr>
            <th class="px-3 py-2 text-left">Level</th>
            <th class="px-3 py-2 text-right">Open Jobs</th>
            <th class="px-3 py-2 text-right">Applicants</th>
            <th class="px-3 py-2 text-right">Hired</th>
            <th class="px-3 py-2 text-right">Avg SLA (days)</th>
            <th class="px-3 py-2 text-right">Success Rate</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($levelStats as $row)
            <tr>
              <td class="px-3 py-2 font-medium text-slate-800">{{ $row['level_label'] }}</td>
              <td class="px-3 py-2 text-right text-slate-700">{{ $row['open_jobs'] }}</td>
              <td class="px-3 py-2 text-right text-slate-700">{{ $row['applicants'] }}</td>
              <td class="px-3 py-2 text-right text-slate-700">{{ $row['hired'] }}</td>
              <td class="px-3 py-2 text-right text-slate-700">{{ number_format((float) $row['avg_sla_days'], 1) }}</td>
              <td class="px-3 py-2 text-right text-slate-700">{{ $row['success_rate'] }}%</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-3 py-6 text-center text-slate-500">Belum ada data level performance.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <section class="p-4 bg-white border shadow-sm rounded-2xl">
    <div class="flex flex-col gap-2 mb-4 md:flex-row md:items-end md:justify-between">
      <div>
        <h2 class="text-sm font-semibold text-slate-900">Interview Selesai per Periode</h2>
        <p class="text-[11px] text-slate-500">Menampilkan interview yang waktunya sudah lewat: kapan interview, kandidat, lowongan, site, mode, dan interviewer/PIC.</p>
      </div>
      <div class="grid grid-cols-3 gap-2 text-center">
        <div class="rounded-xl bg-[#f8f5f1] px-4 py-2">
          <div class="text-[10px] uppercase tracking-[0.18em] text-[#8b5e3c]">Total</div>
          <div class="text-lg font-bold text-[#8b5e3c]">{{ (int) ($interviewTotals['total'] ?? 0) }}</div>
        </div>
        <div class="px-4 py-2 rounded-xl bg-sky-50">
          <div class="text-[10px] uppercase tracking-[0.18em] text-sky-700">Online</div>
          <div class="text-lg font-bold text-sky-700">{{ (int) ($interviewTotals['online'] ?? 0) }}</div>
        </div>
        <div class="px-4 py-2 rounded-xl bg-emerald-50">
          <div class="text-[10px] uppercase tracking-[0.18em] text-emerald-700">Onsite</div>
          <div class="text-lg font-bold text-emerald-700">{{ (int) ($interviewTotals['onsite'] ?? 0) }}</div>
        </div>
      </div>
    </div>

    <div class="overflow-x-auto border rounded-2xl border-slate-200">
      <table class="min-w-[1050px] w-full text-sm">
        <thead class="text-xs uppercase bg-slate-50 text-slate-500">
          <tr>
            <th class="px-3 py-2 text-left">Waktu</th>
            <th class="px-3 py-2 text-left">Kandidat</th>
            <th class="px-3 py-2 text-left">Lowongan</th>
            <th class="px-3 py-2 text-left">Site</th>
            <th class="px-3 py-2 text-left">Interview</th>
            <th class="px-3 py-2 text-left">Mode</th>
            <th class="px-3 py-2 text-left">Interviewer / PIC</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($interviewRows as $row)
            <tr class="hover:bg-[#f8f5f1]/70">
              <td class="px-3 py-3 font-semibold text-slate-900">
                {{ $row['date'] }}
                @if($row['end'] && $row['end'] !== '-')
                  <span class="text-slate-400">- {{ $row['end'] }}</span>
                @endif
              </td>
              <td class="px-3 py-3">
                <div class="font-semibold text-slate-900">{{ $row['candidate'] }}</div>
                <div class="text-xs text-slate-500">{{ $row['email'] }}</div>
              </td>
              <td class="px-3 py-3 text-slate-700">{{ $row['job'] }}</td>
              <td class="px-3 py-3 text-slate-700">{{ $row['site'] }}</td>
              <td class="px-3 py-3 font-medium text-slate-800">{{ $row['title'] }}</td>
              <td class="px-3 py-3">
                <span class="rounded-full bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">{{ strtoupper($row['mode']) }}</span>
              </td>
              <td class="px-3 py-3 text-slate-700">{{ $row['interviewers'] }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-3 py-8 text-center text-slate-500">Belum ada interview selesai pada periode ini.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <section class="p-4 bg-white border shadow-sm rounded-2xl">
    <div class="flex flex-col gap-2 mb-4 md:flex-row md:items-end md:justify-between">
      <div>
        <h2 class="text-sm font-semibold text-slate-900">MPP Fulfillment & Stage Progress</h2>
        <p class="text-[11px] text-slate-500">Format monitoring MPP: MPP dari openings job, QTY dari hired, DEV = QTY - MPP, UPDATE DEV = Total Progress + DEV.</p>
      </div>
      <span class="text-[11px] font-semibold text-[#8b5e3c]">{{ $mppRows->count() }} posisi</span>
    </div>

    <div class="overflow-x-auto border rounded-2xl border-slate-200">
      <table class="min-w-[1500px] w-full text-xs">
        <thead class="text-[10px] uppercase text-slate-600">
          <tr class="bg-[#f8f5f1]">
            <th rowspan="2" class="sticky left-0 z-20 px-3 py-3 text-left bg-[#f8f5f1] border-r border-slate-200">No</th>
            <th rowspan="2" class="px-3 py-3 text-left">Site</th>
            <th rowspan="2" class="px-3 py-3 text-left">Departement</th>
            <th rowspan="2" class="px-3 py-3 text-left">Level</th>
            <th rowspan="2" class="px-3 py-3 text-left">Jabatan</th>
            <th rowspan="2" class="px-3 py-3 text-right">MPP</th>
            <th rowspan="2" class="px-3 py-3 text-right">QTY</th>
            <th rowspan="2" class="px-3 py-3 text-right">% Pemenuhan</th>
            <th rowspan="2" class="px-3 py-3 text-right">DEV</th>
            <th colspan="10" class="px-3 py-2 text-center border-b border-slate-200">Stage Progress</th>
            <th rowspan="2" class="px-3 py-3 text-right">Update DEV</th>
          </tr>
          <tr class="bg-white">
            <th class="px-3 py-2 text-right">Screening CV</th>
            <th class="px-3 py-2 text-right">Interview HR</th>
            <th class="px-3 py-2 text-right">Interview User</th>
            <th class="px-3 py-2 text-right">Psychological/Post/Skill Test</th>
            <th class="px-3 py-2 text-right">OL</th>
            <th class="px-3 py-2 text-right">MCU</th>
            <th class="px-3 py-2 text-right">FU MCU</th>
            <th class="px-3 py-2 text-right">Waiting Inbound</th>
            <th class="px-3 py-2 text-right">Travel</th>
            <th class="px-3 py-2 text-right">Total Progress</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($mppRows as $row)
            <tr class="hover:bg-[#f8f5f1]/70">
              <td class="sticky left-0 z-10 px-3 py-2 font-semibold bg-white border-r border-slate-100">{{ $row['no'] }}</td>
              <td class="px-3 py-2 text-slate-600">{{ $row['site'] }}</td>
              <td class="px-3 py-2 font-semibold text-slate-800">{{ $row['department'] }}</td>
              <td class="px-3 py-2 text-slate-700">{{ $row['level'] }}</td>
              <td class="px-3 py-2 font-medium text-slate-900">{{ $row['position'] }}</td>
              <td class="px-3 py-2 text-right">{{ number_format($row['mpp'], 0, ',', '.') }}</td>
              <td class="px-3 py-2 text-right">{{ number_format($row['qty'], 0, ',', '.') }}</td>
              <td class="px-3 py-2 text-right">
                <span class="rounded-full px-2 py-0.5 font-semibold {{ $row['fulfillment'] >= 100 ? 'bg-emerald-50 text-emerald-700' : ($row['fulfillment'] >= 75 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                  {{ $row['fulfillment'] }}%
                </span>
              </td>
              <td class="px-3 py-2 text-right font-semibold {{ $row['dev'] < 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $row['dev'] }}</td>
              <td class="px-3 py-2 text-right">{{ $row['screening'] }}</td>
              <td class="px-3 py-2 text-right">{{ $row['hr'] }}</td>
              <td class="px-3 py-2 text-right">{{ $row['user'] }}</td>
              <td class="px-3 py-2 text-right">{{ $row['practical_ground'] }}</td>
              <td class="px-3 py-2 text-right">{{ $row['ol'] }}</td>
              <td class="px-3 py-2 text-right">{{ $row['mcu'] }}</td>
              <td class="px-3 py-2 text-right">{{ $row['fu_mcu'] }}</td>
              <td class="px-3 py-2 text-right">{{ $row['waiting_inbound'] }}</td>
              <td class="px-3 py-2 text-right">{{ $row['travel'] }}</td>
              <td class="px-3 py-2 font-semibold text-right text-[#8b5e3c]">{{ $row['total_progress'] }}</td>
              <td class="px-3 py-2 text-right font-semibold {{ $row['update_dev'] < 0 ? 'text-red-700' : 'text-slate-800' }}">{{ $row['update_dev'] }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="20" class="px-3 py-8 text-center text-slate-500">Belum ada open job untuk site ini.</td>
            </tr>
          @endforelse
        </tbody>
        <tfoot class="font-semibold bg-slate-50 text-slate-900">
          <tr>
            <td class="sticky left-0 z-10 px-3 py-3 bg-slate-50 border-r border-slate-200" colspan="5">Grand Total</td>
            <td class="px-3 py-3 text-right">{{ number_format((int) ($mppTotals['mpp'] ?? 0), 0, ',', '.') }}</td>
            <td class="px-3 py-3 text-right">{{ number_format((int) ($mppTotals['qty'] ?? 0), 0, ',', '.') }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['fulfillment'] ?? 0) }}%</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['dev'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['screening'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['hr'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['user'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['practical_ground'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['ol'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['mcu'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['fu_mcu'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['waiting_inbound'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['travel'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right text-[#8b5e3c]">{{ (int) ($mppTotals['total_progress'] ?? 0) }}</td>
            <td class="px-3 py-3 text-right">{{ (int) ($mppTotals['update_dev'] ?? 0) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const primary = '#a77d52';
const secondary = '#8b9f6f';
const accent = '#2f6f6d';
const danger = '#b45309';

const sourceLabels = @json(array_values($sourceLabels));
const sourceValues = @json(array_values($sourceBreakdown->toArray()));
const educationLabels = @json(array_values($educationLabels));
const educationValues = @json(array_values($educationBreakdown->toArray()));
const levelLabels = @json($slaLevelStats->pluck('level_label')->toArray());
const levelSlaValues = @json($slaLevelStats->pluck('avg_sla_days')->map(fn ($value) => (float) $value)->toArray());
const levelSuccessValues = @json($slaLevelStats->pluck('success_rate')->toArray());
const failureLabels = @json($failureRows->pluck('stage_key')->toArray());
const failureValues = @json($failureRows->pluck('total')->toArray());
const trendLabels = @json(($applicationTrend ?? collect())->keys()->toArray());
const trendValues = @json(($applicationTrend ?? collect())->values()->toArray());

new Chart(document.getElementById('slaLevelChart'), {
  type: 'bar',
  data: {
    labels: levelLabels,
    datasets: [{
      label: 'Avg SLA Days',
      data: levelSlaValues,
      borderColor: primary,
      backgroundColor: primary,
      borderWidth: 1,
      borderRadius: 8,
      barPercentage: 0.7,
      categoryPercentage: 0.7,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { mode: 'index', intersect: false },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          precision: 0,
          callback: (value) => Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 }),
        },
      },
      x: { grid: { display: false }, ticks: { maxRotation: 0, minRotation: 0, autoSkip: true } },
    }
  }
});

new Chart(document.getElementById('sourceChart'), {
  type: 'doughnut',
  data: {
    labels: sourceLabels,
    datasets: [{
      data: sourceValues,
      backgroundColor: [primary, secondary, accent, '#38bdf8', '#f472b6', '#f59e0b', '#94a3b8', '#64748b'],
      borderWidth: 0,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '82%',
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          usePointStyle: true,
          boxWidth: 8,
          boxHeight: 8,
          padding: 6,
          font: { size: 9 },
        }
      }
    }
  }
});

new Chart(document.getElementById('educationChart'), {
  type: 'bar',
  data: {
    labels: educationLabels,
    datasets: [{
      label: 'Candidates',
      data: educationValues,
      backgroundColor: accent,
      borderRadius: 10,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          precision: 0,
          callback: (value) => Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 }),
        },
      },
    }
  }
});

new Chart(document.getElementById('genderChart'), {
  type: 'doughnut',
  data: {
    labels: ['Male', 'Female', 'Other'],
    datasets: [{
      data: [
        {{ $genderBreakdown['male'] ?? 0 }},
        {{ $genderBreakdown['female'] ?? 0 }},
        {{ $genderBreakdown['other'] ?? 0 }},
      ],
      backgroundColor: ['#60a5fa', '#f472b6', '#94a3b8'],
      borderWidth: 0,
    }]
  },
  options: { responsive: true, maintainAspectRatio: false, cutout: '72%' }
});

new Chart(document.getElementById('failureStageChart'), {
  type: 'bar',
  data: {
    labels: failureLabels,
    datasets: [{
      label: 'Failed Stage Count',
      data: failureValues,
      backgroundColor: danger,
      borderRadius: 10,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          precision: 0,
          callback: (value) => Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 }),
        },
      },
    }
  }
});

new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: trendLabels,
    datasets: [{
      label: 'Applications',
      data: trendValues,
      borderColor: primary,
      backgroundColor: primary + '20',
      fill: true,
      tension: 0.35,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          precision: 0,
          callback: (value) => Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 }),
        },
      },
    }
  }
});
</script>

@endsection
