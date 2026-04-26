{{-- resources/views/jobs/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Lowongan • karir-andalan')

@php
    use Illuminate\Support\Str;

    // ===== Query helpers (whitelisted) =====
    $qDivision = trim((string) request('division', ''));
    $qSite = trim((string) request('site', ''));
    $qCompany = trim((string) request('company', '')); // company code
    $qTerm = trim((string) request('term', ''));
    $qSort = trim((string) request('sort', ''));
    $hasAny = $qDivision || $qSite || $qCompany || $qTerm || $qSort;

    // Keep-only params antar form/link
    $keepParams = array_filter(
        request()->only(['division', 'site', 'company', 'term', 'sort']),
        fn($v) => filled($v)
    );

    // Hapus satu param
    $rm = fn(string $key) => route('jobs.index', collect($keepParams)->except($key)->all());
    $resetUrl = route('jobs.index');
    $total = method_exists($jobs, 'total') ? (int) $jobs->total() : (int) $jobs->count();

    // ===== Ikon & warna per divisi (base: biru; aksen lain halus) =====
    $deptIcons = [
        'HR' => ['icon' => 'i-hr', 'bg' => 'bg-sky-50', 'fg' => 'text-sky-700', 'ring' => 'ring-sky-200'],
        'Plant' => ['icon' => 'i-plant', 'bg' => 'bg-amber-50', 'fg' => 'text-amber-700', 'ring' => 'ring-amber-200'],
        'SCM' => ['icon' => 'i-scm', 'bg' => 'bg-indigo-50', 'fg' => 'text-indigo-700', 'ring' => 'ring-indigo-200'],
        'IT' => ['icon' => 'i-it', 'bg' => 'bg-blue-50', 'fg' => 'text-blue-700', 'ring' => 'ring-blue-200'],
        'Finance' => ['icon' => 'i-finance', 'bg' => 'bg-slate-50', 'fg' => 'text-slate-700', 'ring' => 'ring-slate-200'],
        'QA' => ['icon' => 'i-qa', 'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700', 'ring' => 'ring-emerald-200'],
        'HSE' => ['icon' => 'i-hse', 'bg' => 'bg-cyan-50', 'fg' => 'text-cyan-700', 'ring' => 'ring-cyan-200'],
        'GA' => ['icon' => 'i-ga', 'bg' => 'bg-fuchsia-50', 'fg' => 'text-fuchsia-700', 'ring' => 'ring-fuchsia-200'],
        'Legal' => ['icon' => 'i-legal', 'bg' => 'bg-violet-50', 'fg' => 'text-violet-700', 'ring' => 'ring-violet-200'],
        'Marketing' => ['icon' => 'i-marketing', 'bg' => 'bg-orange-50', 'fg' => 'text-orange-700', 'ring' => 'ring-orange-200'],
        'Sales' => ['icon' => 'i-sales', 'bg' => 'bg-teal-50', 'fg' => 'text-teal-700', 'ring' => 'ring-teal-200'],
        'R&D' => ['icon' => 'i-rnd', 'bg' => 'bg-rose-50', 'fg' => 'text-rose-700', 'ring' => 'ring-rose-200'],
    ];
    $deptMeta = function (?string $div) use ($deptIcons) {
        $key = $div ? strtoupper(trim($div)) : '';
        $aliases = ['HRGA' => 'HR', 'HUMAN RESOURCE' => 'HR', 'HUMAN RESOURCES' => 'HR', 'PLANT ENGINEERING' => 'PLANT'];
        $key = $aliases[$key] ?? $key;
        return $deptIcons[$key] ?? ['icon' => 'i-briefcase', 'bg' => 'bg-slate-50', 'fg' => 'text-slate-700', 'ring' => 'ring-slate-200'];
    };

    // rail warna kiri item (biru dominan + aksen lembut)
    $railColors = ['border-blue-500', 'border-sky-400', 'border-indigo-400', 'border-emerald-400', 'border-amber-400'];

    $extractSkills = function ($job) {
        $attrs = $job->getAttributes();
        $raw = $job->skills
            ?? ($attrs['skills'] ?? null)
            ?? ($job->tags ?? ($attrs['tags'] ?? null))
            ?? ($job->keywords ?? ($attrs['keywords'] ?? null));

        $list = collect();

        if (is_array($raw)) {
            $list = collect($raw);
        } elseif (is_string($raw)) {
            $raw = trim($raw);
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $list = collect($decoded);
                } else {
                    $list = collect(preg_split('/\s*[,;|]\s*/', $raw));
                }
            }
        }

        return $list
            ->map(function ($v) {
                if (is_string($v))
                    return trim($v);
                if (is_array($v))
                    return trim((string) ($v['name'] ?? $v['label'] ?? ''));
                return '';
            })
            ->filter()
            ->unique()
            ->take(10)
            ->values();
    };

    /** Helper audit: nama pembuat & waktu */
    $auditWho = function ($job) {
        $creatorName = $job->creator->name ?? $job->created_by_name ?? $job->created_by ?? null;
        $updaterName = $job->updater->name ?? $job->updated_by_name ?? $job->updated_by ?? null;

        return [
            'creator' => $creatorName ? Str::limit($creatorName, 40) : null,
            'updater' => $updaterName ? Str::limit($updaterName, 40) : null,
        ];
    };
@endphp

@section('content')

    @once
        <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
          <symbol id="i-filter" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4" />
          </symbol>
          <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="11" cy="11" r="7" stroke-width="2" />
            <path d="M21 21l-4.3-4.3" stroke-width="2" stroke-linecap="round" />
          </symbol>
          <symbol id="i-x" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6" />
          </symbol>
          <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z" />
            <path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1" stroke-width="1.8" />
          </symbol>
          <symbol id="i-map" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M9 20l-5-2V6l5 2 6-2 5 2v12l-5-2-6 2zM14 4v14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </symbol>
          <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="9" stroke-width="2" />
            <path d="M12 7v5l3 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </symbol>
          <symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="10" cy="8" r="3" stroke-width="2" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </symbol>
          <symbol id="i-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M6 9l6 6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </symbol>
          <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </symbol>
          <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </symbol>
          <symbol id="i-location" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 2C8 2 5 5 5 9c0 5 7 11 7 11s7-6 7-11c0-4-3-7-7-7z" stroke-width="2" />
            <circle cx="12" cy="9" r="2" stroke-width="2" />
          </symbol>
          <symbol id="i-briefcase-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="3" y="7" width="18" height="13" rx="2" stroke-width="2" />
            <path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" stroke-width="2" />
          </symbol>
          <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2" />
            <path d="M16 2v4M8 2v4M3 10h18" stroke-width="2" stroke-linecap="round" />
          </symbol>
        </svg>
    @endonce

    <div class="mx-auto w-full max-w-[1480px] px-4 md:px-6 lg:px-8">
      <section class="mb-4 overflow-hidden shadow-sm rounded-2xl" style="background: #a77d52;">
        <div class="text-white">
          <div class="flex flex-col gap-4 p-5 md:p-6 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
              <h1 class="text-xl font-semibold">Lowongan Pekerjaan</h1>
              <p class="text-sm text-white/80 mt-0.5">Temukan pekerjaan impianmu</p>
            </div>

            <div class="w-full md:w-[600px] flex items-stretch gap-2">
              <form method="GET" action="{{ route('jobs.index') }}" role="search" class="flex-1">
                <label for="job-search" class="sr-only">Cari lowongan</label>
                <div class="relative">
                  <svg class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none left-3 top-1/2 text-white/50">
                    <use href="#i-search" />
                  </svg>
                  <input
                    id="job-search"
                    name="term"
                    value="{{ e($qTerm) }}"
                    placeholder="Cari posisi, divisi, atau site..."
                    class="w-full rounded-xl border border-white/30 bg-white/10 py-2.5 pl-9 pr-28 text-sm text-white placeholder-white/60 outline-none backdrop-blur-sm
                           focus:ring-2 focus:ring-white/50 focus:bg-white/15 transition-all"
                    autocomplete="off" />
                  <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                    @if($qTerm)
                        <a href="{{ $rm('term') }}" class="rounded-md border border-white/30 bg-white/10 p-1.5 hover:bg-white/20 transition-colors">
                          <svg class="w-4 h-4 text-white">
                            <use href="#i-x" />
                          </svg>
                        </a>
                    @endif
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold text-[#a77d52] bg-white hover:bg-white/90 transition-colors">
                      <svg class="w-4 h-4">
                        <use href="#i-search" />
                      </svg>
                      <span>Cari</span>
                    </button>
                  </div>
                </div>
                @foreach(['division', 'site', 'company', 'sort'] as $keep)
                    @if(!empty($keepParams[$keep]))
                        <input type="hidden" name="{{ $keep }}" value="{{ e($keepParams[$keep]) }}">
                    @endif
                @endforeach
              </form>

              <button id="btn-filter" type="button" class="inline-flex items-center gap-1 px-4 py-2 text-sm font-semibold text-[#a77d52] bg-white rounded-xl hover:bg-white/90 transition-colors shrink-0">
                <svg class="w-4 h-4">
                  <use href="#i-filter" />
                </svg>
                <span>Filter</span>
              </button>
            </div>
          </div>

          <div class="h-px bg-white/20"></div>

          <div id="filter-panel" class="hidden p-4 md:p-5">
            <form method="GET" class="grid gap-3 md:grid-cols-4" aria-label="Filter Lowongan">
              <div>
                <label class="block mb-1 text-xs font-medium uppercase text-white/80">Divisi</label>
                <input name="division" value="{{ e($qDivision) }}" placeholder="Plant / SCM / HR" class="w-full px-3 py-2 text-sm border rounded-lg bg-white/10 border-white/30 text-white placeholder-white/50 focus:ring-2 focus:ring-white/50" />
              </div>
              <div>
                <label class="block mb-1 text-xs font-medium uppercase text-white/80">Site</label>
                <input name="site" value="{{ e($qSite) }}" placeholder="DBK / POS" class="w-full px-3 py-2 text-sm border rounded-lg bg-white/10 border-white/30 text-white placeholder-white/50 focus:ring-2 focus:ring-white/50" />
              </div>
              <div>
                <label class="block mb-1 text-xs font-medium uppercase text-white/80">Company</label>
                <input name="company" value="{{ e($qCompany) }}" placeholder="ANDALAN" class="w-full px-3 py-2 text-sm border rounded-lg bg-white/10 border-white/30 text-white placeholder-white/50 focus:ring-2 focus:ring-white/50" />
              </div>
              <div class="flex items-end gap-2">
                <button class="flex-1 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-[#a77d52] hover:bg-white/90 transition-colors">Terapkan</button>
                <a href="{{ $resetUrl }}" class="px-3 py-2 text-sm border rounded-lg border-white/30 text-white hover:bg-white/10 transition-colors">Reset</a>
              </div>
            </form>
          </div>
        </div>
      </section>

      @if($jobs->count())
          @php $firstId = optional($jobs->first())->id; @endphp

          <section x-data="{ open: @js($firstId), select(id){ this.open = id; const d=document.getElementById('detail-'+id); if(!d) return; const pr = window.matchMedia('(prefers-reduced-motion: reduce)').matches; const top = d.getBoundingClientRect().top + window.scrollY - 80; window.scrollTo({top, behavior: pr ? 'auto' : 'smooth'}); } }" class="grid gap-4 md:grid-cols-[400px,1fr]">

            <aside class="bg-white shadow-sm rounded-2xl md:sticky md:top-4 md:self-start overflow-hidden" style="border: 2px solid #a77d52;">
              <div class="p-4 border-b flex items-center justify-between" style="border-color: #a77d52;">
                <div>
                  <p class="text-xs" style="color: #a77d52;">Total</p>
                  <p class="text-lg font-semibold" style="color: #a77d52;">{{ $total }}</p>
                </div>
                <form method="GET" class="flex items-center gap-2">
                  @foreach(['division', 'site', 'company', 'term'] as $keep)
                      @if(!empty($keepParams[$keep]))
                          <input type="hidden" name="{{ $keep }}" value="{{ e($keepParams[$keep]) }}">
                      @endif
                  @endforeach
                  <select name="sort" class="h-8 text-xs appearance-none rounded-lg border px-2 py-1" style="border-color: #a77d52; color: #a77d52;" style2="border-color: #a77d52;">
                    <option value="">Terbaru</option>
                    <option value="oldest" @selected($qSort === 'oldest')>Terlama</option>
                    <option value="title" @selected($qSort === 'title')>Judul A-Z</option>
                  </select>
                  <button type="submit" class="h-8 px-3 text-xs font-semibold text-white rounded-lg hover:opacity-90 transition" style="background: #a77d52;">OK</button>
                </form>
              </div>

              <ul class="divide-y max-h-[70vh] overflow-y-auto" style="border-color: #a77d52;">
                @foreach($jobs as $job)
                    @php
                        $typeRaw = $job->employment_type ?? '';
                        $type = strtoupper($typeRaw ?: '-');
                        $companyId = $job->company_id;
                        $code = $job->code;
                        $title = $job->title;
                        $division = $job->division;
                        $level = $job->level;
                        $status = $job->status;
                        $openings = (int) $job->openings;
                        $createdAt = $job->created_at?->format('d M Y');
                        $siteLabel = $job->site->name ?? $job->site_name ?? $job->getAttribute('site_name') ?? $job->site_code ?? '—';
                        $who = $auditWho($job);
                    @endphp

                    <li>
                      <button type="button" @click="select('{{ $job->id }}')" :class="open==='{{ $job->id }}' ? 'bg-[#a77d52] text-white' : 'bg-white text-[#a77d52]'" class="w-full p-4 text-left transition-all group" style="border-bottom: 1px solid #a77d52;">
                        <div class="flex items-start gap-3">
                          <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-white" :class="open==='{{ $job->id }}' ? 'bg-white text-[#a77d52]' : 'bg-[#a77d52]'">
                            <svg class="w-5 h-5"><use href="#i-briefcase" /></svg>
                          </div>
                          <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                              <p class="truncate text-sm font-semibold">{{ e($title) }}</p>
                              @if($code)
                                  <span class="shrink-0 text-[10px] px-1.5 py-0.5 rounded text-white" style="background: #a77d52;">{{ e($code) }}</span>
                              @endif
                            </div>
                            <div class="flex items-center gap-2 mt-1 text-xs">
                              <span class="flex items-center gap-1"><svg class="w-3 h-3"><use href="#i-briefcase-2" /></svg>{{ e($job->company->name ?? '—') }}</span>
                              <span class="flex items-center gap-1"><svg class="w-3 h-3"><use href="#i-location" /></svg>{{ e($siteLabel) }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1.5">
                              <span class="text-[10px] px-2 py-0.5 rounded-full text-white" style="background: #a77d52;">{{ e($type) }}</span>
                              @if(strtolower((string) $status) === 'open')
                                  <span class="text-[10px] px-2 py-0.5 rounded-full" style="border: 1px solid #a77d52; color: #a77d52;">OPEN</span>
                              @endif
                            </div>
                          </div>
                          <svg class="w-4 h-4" :class="open==='{{ $job->id }}' ? 'rotate-90' : ''">
                            <use href="#i-chevron" />
                          </svg>
                        </div>
                      </button>
                    </li>
                @endforeach
              </ul>
            </aside>

            <div class="space-y-4">
              @foreach($jobs as $job)
                  @php
                    $typeRaw = $job->employment_type ?? '';
                    $type = strtoupper($typeRaw ?: '-');
                    $siteLabel = $job->site->name ?? $job->site_name ?? $job->site_code ?? '—';
                    $skills = $extractSkills($job);
                    $who = $auditWho($job);
                    $desc = $job->getAttributes()['description'] ?? null;
                  @endphp

                  <article x-show="open==='{{ $job->id }}'" x-cloak id="detail-{{ $job->id }}" class="bg-white shadow-sm rounded-2xl overflow-hidden">
                    <div class="p-5 text-white" style="background: linear-gradient(135deg, #a77d52 0%, #8c6843 100%);">
                      <div class="flex items-center justify-between">
                        <div>
                          <h2 class="text-lg font-semibold text-white">{{ e($job->title) }}</h2>
                          <p class="text-white/80 text-sm mt-0.5">{{ e($job->company->name ?? '—') }} • {{ e($siteLabel) }}</p>
                        </div>
                        <a href="{{ route('jobs.show', $job) }}?apply=1#apply" class="px-4 py-2 text-sm font-semibold rounded-lg transition text-white" style="background: #a77d52;">Lamar</a>
                      </div>
                    </div>

                    <div class="p-5 space-y-4" style="background: #a77d52;">
                      <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 rounded-xl text-center" style="border: 1px solid #a77d52;">
                          <svg class="w-5 h-5 mx-auto text-white mb-1" style="color: #a77d52;"><use href="#i-location" /></svg>
                          <p class="text-xs text-white/70">Lokasi</p>
                          <p class="text-sm font-medium text-white truncate">{{ e($siteLabel) }}</p>
                        </div>
                        <div class="p-3 rounded-xl text-center" style="border: 1px solid #a77d52;">
                          <svg class="w-5 h-5 mx-auto text-white mb-1" style="color: #a77d52;"><use href="#i-users" /></svg>
                          <p class="text-xs text-white/70">Kebutuhan</p>
                          <p class="text-sm font-medium text-white">{{ (int) $job->openings }} org</p>
                        </div>
                        <div class="p-3 rounded-xl text-center" style="border: 1px solid #a77d52;">
                          <svg class="w-5 h-5 mx-auto text-white mb-1" style="color: #a77d52;"><use href="#i-briefcase-2" /></svg>
                          <p class="text-xs text-white/70">Tipe</p>
                          <p class="text-sm font-medium text-white">{{ e($type) }}</p>
                        </div>
                      </div>

                      @if($job->division || $job->level)
                      <div class="flex flex-wrap gap-2">
                        @if($job->division)
                        <span class="text-xs px-3 py-1 rounded-full text-white" style="background: #a77d52;">{{ e($job->division) }}</span>
                        @endif
                        @if($job->level)
                        <span class="text-xs px-3 py-1 rounded-full text-white" style="background: #a77d52; opacity: 0.7;">{{ e($job->level) }}</span>
                        @endif
                        @if($code)
                        <span class="text-xs px-3 py-1 rounded-full text-white" style="background: #a77d52; opacity: 0.7;">{{ e($code) }}</span>
                        @endif
                      </div>
                      @endif

                      @if($skills->isNotEmpty())
                      <div>
                        <h3 class="text-sm font-semibold text-white mb-2">Skills</h3>
                        <div class="flex flex-wrap gap-1.5">
                          @foreach($skills->take(6) as $sk)
                              <span class="text-xs px-2.5 py-1 rounded-lg text-white" style="background: #a77d52; opacity: 0.8;">{{ e($sk) }}</span>
                          @endforeach
                        </div>
                      </div>
                      @endif

                      @if($desc)
                      <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-white">
                          <span>Deskripsi</span>
                          <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''"><use href="#i-chevron" /></svg>
                        </button>
                        <div x-show="open" x-collapse class="mt-2 text-sm text-white/80 prose prose-sm max-w-none">{!! $desc !!}</div>
                      </div>
                      @endif

                      <div class="pt-3 border-t flex items-center justify-between text-xs text-white/50" style="border-color: #a77d52;">
                        <span>@if($who['creator']){{ e($who['creator']) }}@endif</span>
                        <span>{{ $createdAt }}</span>
                      </div>
                    </div>
                  </article>
              @endforeach
            </div>
          </section>

          <section class="p-4 mt-4 bg-white shadow-sm rounded-2xl" style="border: 2px solid #a77d52;">
            <div class="flex items-center justify-between">
              <p class="text-sm" style="color: #a77d52;">Menampilkan {{ $jobs->count() }} lowongan</p>
            </div>
          </section>
      @else
          <section class="p-12 text-center bg-white shadow-sm rounded-2xl" style="border: 2px solid #a77d52;">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" style="background: #a77d52;">
              <svg class="w-8 h-8 text-white"><use href="#i-search" /></svg>
            </div>
            <h3 class="text-lg font-semibold text-white">Tidak ada lowongan</h3>
            <p class="text-sm text-white/80 mt-1">Coba ubah keyword atau filter</p>
            <div class="flex items-center justify-center gap-2 mt-4">
              <a href="{{ $resetUrl }}" class="px-4 py-2 text-sm font-semibold text-white rounded-lg hover:opacity-90" style="background: #a77d52;">Reset</a>
            </div>
          </section>
      @endif
    </div>

    @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const pnl = document.getElementById('filter-panel');
        const btn = document.getElementById('btn-filter');
        btn && btn.addEventListener('click', () => pnl.classList.toggle('hidden'));
      });
    </script>
    @endpush
@endsection