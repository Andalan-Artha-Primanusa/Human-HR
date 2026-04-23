{{-- resources/views/admin/offers/index.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Offers'])

@php
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200
@endphp


@section('content')
    @once
          {{-- Sprite ikon kecil untuk pagination --}}
          <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
            <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
          </svg>
    @endonce

    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- HEADER + FILTER seperti halaman Sites --}}
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative">
          <div class="w-full h-20 sm:h-24" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
          <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

          <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Offers</h1>
              <p class="text-xs sm:text-sm text-white/90">Daftar draft/final offer untuk kandidat.</p>
            </div>
          </div>
        </div>

        @php
            $q = $q ?? request('q');
            $selStatus = $status ?? request('status');
            $opts = [
                '' => 'Semua Status',
                'draft' => 'Draft',
                'sent' => 'Sent',
                'accepted' => 'Accepted',
                'rejected' => 'Rejected',
            ];
        @endphp

        <div class="p-6 border-t md:p-7 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]" style="border-color: {{ $BORD }}">
          <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_200px_auto] md:items-end" role="search" aria-label="Filter Offers">
            <label class="sr-only" for="q">Cari</label>
            <input id="q" name="q" value="{{ e($q) }}"
                   class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}" placeholder="Cari kandidat / posisi…" autocomplete="off">

            <label class="sr-only" for="status">Status</label>
            <select id="status" name="status"
                    class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $ACCENT }}">
              @foreach($opts as $k => $v)
                <option value="{{ $k }}" @selected($selStatus === $k)>{{ $v }}</option>
              @endforeach
            </select>

            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
              <button type="submit"
                      class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] shadow-sm hover:brightness-105 focus:outline-none focus:ring-2"
                      style="--tw-ring-color: {{ $ACCENT }}">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
                  <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Cari
              </button>

              @if(request()->filled('q') || request()->filled('status'))
                <a href="{{ route('admin.offers.index') }}"
                   class="inline-flex items-center justify-center px-5 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 hover:bg-slate-50 text-slate-900">
                  Reset
                </a>
              @endif
            </div>
          </form>
        </div>
      </section>

      {{-- FLASH --}}
      @if(session('success'))
        <div class="px-4 py-3 text-green-700 border border-green-200 rounded-xl bg-green-50">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="px-4 py-3 text-red-700 border border-red-200 rounded-xl bg-red-50">{{ session('error') }}</div>
      @endif

      {{-- TABEL --}}
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="overflow-x-auto">
          @if(($offers->count() ?? 0) > 0)
                <table class="min-w-full text-sm">
                  {{-- HEADER --}}
                  <thead class="text-white bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)]">
                  <tr>
                    <th class="px-4 py-3 text-left">Kandidat</th>
                    <th class="px-4 py-3 text-left">Posisi</th>
                    <th class="w-24 px-4 py-3 text-left">Site</th>
                    <th class="w-32 px-4 py-3 text-center">Gross</th>
                    <th class="px-4 py-3 text-center w-36">Allowance</th>
                    <th class="px-4 py-3 text-center w-28">Status</th>
                    <th class="w-32 px-4 py-3 text-left">Dibuat</th>
                    <th class="w-40 px-4 py-3 text-right">Aksi</th>
                  </tr>
                </thead>

                {{-- BODY --}}
                <tbody class="divide-y divide-slate-100">
                  @foreach($offers as $offer)
                    @php
                        $app = $offer->application;
                        $user = $app?->user?->name ?? $app?->candidate?->name ?? ($offer->candidate_name ?? '—');
                        $email = $app?->candidate?->email ?? null;
                        $title = $app?->job?->title ?? '—';
                        $site = $app?->job?->site?->code ?? $app?->job?->site_code ?? '—';
                        $grossV = (float) (\Illuminate\Support\Arr::get($offer->salary, 'gross', 0));
                        $allowV = (float) (\Illuminate\Support\Arr::get($offer->salary, 'allowance', 0));
                        $gross = number_format($grossV, 0, ',', '.');
                        $allow = number_format($allowV, 0, ',', '.');

                        $badge = match ($offer->status) {
                            'accepted' => 'bg-green-50 text-green-700',
                            'rejected' => 'bg-rose-50 text-rose-700',
                            'sent' => 'bg-blue-50 text-blue-700',
                            default => 'bg-amber-50 text-amber-700',
                        };
                    @endphp

                    <tr class="align-top hover:bg-[#f8f5f2] transition">

                      {{-- KANDIDAT --}}
                      <td class="px-4 py-3">
                        <div class="font-semibold text-slate-900">{{ e($user) }}</div>
                        @if($email)
                              <div class="text-xs text-slate-500">{{ e($email) }}</div>
                        @endif
                      </td>

                      {{-- POSISI --}}
                      <td class="px-4 py-3">
                        <div class="font-medium text-slate-800">{{ e($title) }}</div>
                        @if(!empty($app?->job?->code))
                              <div class="mt-0.5 text-xs text-slate-500">#{{ e($app->job->code) }}</div>
                        @endif
                      </td>

                      {{-- SITE --}}
                      <td class="px-4 py-3">
                        <span class="px-2 py-1 font-mono rounded-md text-slate-700 bg-slate-100">
                          {{ e($site) }}
                        </span>
                      </td>

                      {{-- GROSS --}}
                      <td class="px-4 py-3 text-center font-semibold text-[#a77d52]">
                        Rp {{ $gross }}
                      </td>

                      {{-- ALLOWANCE --}}
                      <td class="px-4 py-3 font-semibold text-center text-slate-700">
                        Rp {{ $allow }}
                      </td>

                      {{-- STATUS --}}
                      <td class="px-4 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                          {{ strtoupper($offer->status ?? 'draft') }}
                        </span>
                      </td>

                      {{-- TANGGAL --}}
                      <td class="px-4 py-3 text-slate-600">
                        {{ optional($offer->created_at)->format('d M Y') ?? '—' }}
                      </td>

                      {{-- AKSI --}}
                      <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                          <button type="button" 
                            onclick="openEditOfferModal('{{ $offer->id }}', '{{ addslashes($user) }}', '{{ $grossV }}', '{{ $allowV }}', `{{ addslashes($offer->body_template ?? '') }}`, '{{ $offer->status }}', {{ json_encode($offer->meta ?? []) }}, '{{ optional($offer->application->job->company)->name }}', '{{ $offer->application->job->level_label }}', '{{ optional($offer->application->poh)->name }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-sm text-amber-600 font-medium">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Edit
                          </button>

                          @if(Route::has('admin.offers.pdf'))
                                <a href="{{ route('admin.offers.pdf', $offer) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-sm">
                                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <path d="M14 2v6h6"/>
                                  </svg>
                                  PDF
                                </a>
                          @endif
                        </div>
                      </td>

                    </tr>
                  @endforeach
                </tbody>
              </table>

          @else
            {{-- EMPTY STATE --}}
            <section class="p-10 text-center bg-white border border-dashed rounded-2xl border-slate-300">
              <div class="grid w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 place-content-center text-slate-400">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 0 0 4.5 4v16A2.5 2.5 0 0 0 7 22.5h10A2.5 2.5 0 0 0 19.5 20V7.5Z"/>
                </svg>
              </div>
              <div class="font-medium text-slate-700">Belum ada offer.</div>
              <div class="mt-1 text-sm text-slate-500">Coba ubah filter atau buat offer baru.</div>
            </section>
          @endif
        </div>
      </section>

      {{-- PAGINATION (kapsul custom) --}}
      @php
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $offers */
        $hasData = (int) $offers->total() > 0;
      @endphp

      @if($hasData)
        @php
            $perPage = max(1, (int) $offers->perPage());
            $current = (int) $offers->currentPage();
            $last = (int) $offers->lastPage();
            $total = (int) $offers->total();
            $from = ($current - 1) * $perPage + 1;
            $to = min($current * $perPage, $total);

            $pages = [];
            if ($last <= 7) {
                $pages = range(1, $last);
            } else {
                $pages = [1];
                $left = max(2, $current - 1);
                $right = min($last - 1, $current + 1);
                if ($left > 2)
                    $pages[] = '...';
                for ($i = $left; $i <= $right; $i++)
                    $pages[] = $i;
                if ($right < $last - 1)
                    $pages[] = '...';
                $pages[] = $last;
            }

            $pageUrl = function (int $p) use ($offers) {
                return $offers->appends(request()->except('page'))->url($p);
            };
        @endphp

        <section class="p-3 bg-white border shadow-sm rounded-2xl border-slate-200 md:p-4">
          <div class="flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">
            <div class="text-slate-700">
              Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span>
              dari <span class="font-semibold text-slate-900">{{ $total }}</span>
            </div>
            <div class="hidden md:block text-slate-700">
              Showing <span class="font-semibold text-slate-900">{{ $from }}</span>
              to <span class="font-semibold text-slate-900">{{ $to }}</span>
              of <span class="font-semibold text-slate-900">{{ $total }}</span> results
            </div>

            <nav class="ml-auto" aria-label="Pagination">
              <ul class="inline-flex items-stretch overflow-hidden bg-white border rounded-xl border-slate-200">
                {{-- Prev --}}
                <li>
                  @if($current > 1)
                    <a href="{{ $pageUrl($current - 1) }}"
                       class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $ACCENT }}" aria-label="Sebelumnya">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </a>
                  @else
                    <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </span>
                  @endif
                </li>

                {{-- Pages --}}
                @foreach($pages as $p)
                      @if($p === '...')
                        <li class="grid px-3 select-none place-items-center h-9 text-slate-500">…</li>
                      @else
                        @php $isCur = ((int) $p === $current); @endphp
                        <li class="grid place-items-center h-9">
                          @if($isCur)
                            <span class="inline-flex items-center h-full px-3 font-semibold border-l select-none text-slate-900 bg-slate-100 border-slate-200">{{ $p }}</span>
                          @else
                            <a href="{{ $pageUrl((int) $p) }}"
                               class="inline-flex items-center h-full px-3 border-l text-slate-700 hover:bg-slate-50 border-slate-200 focus:outline-none focus:ring-2"
                               style="--tw-ring-color: {{ $ACCENT }}" aria-label="Halaman {{ $p }}">{{ $p }}</a>
                          @endif
                        </li>
                      @endif
                @endforeach

                {{-- Next --}}
                <li class="border-l border-slate-200">
                  @if($current < $last)
                    <a href="{{ $pageUrl($current + 1) }}"
                       class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $ACCENT }}" aria-label="Berikutnya">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                    </a>
                  @else
                    <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                    </span>
                  @endif
                </li>
              </ul>
            </nav>
          </div>
        </section>
      @endif
    </div>
    </div>
    
    {{-- MODAL: EDIT OFFER --}}
    <div id="modal-edit-offer" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-lg overflow-hidden bg-white shadow-2xl rounded-2xl animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900" id="edit-offer-title">Edit Offer</h3>
                    <p class="text-xs text-slate-500">Sesuaikan detail gaji dan pesan penawaran.</p>
                </div>
                <button type="button" onclick="closeEditOfferModal()" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form id="form-edit-offer" method="POST" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                @csrf
                @method('PATCH')
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Perusahaan (PT)</label>
                        <input type="text" name="company" id="edit-company" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Nomor Surat</label>
                        <input type="text" name="doc_no" id="edit-doc-no" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Grade / Level</label>
                        <select name="grade_level" id="edit-grade" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                            <option value="">-- Pilih Level --</option>
                            @foreach(\App\Models\Job::LEVEL_LABELS as $slug => $label)
                                <option value="{{ $label }}">{{ $label }}</option>
                            @endforeach
                            <option value="Non Staff">Non Staff</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">PoH (Tempat Penerimaan)</label>
                        <select name="poh" id="edit-poh" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                            <option value="">-- Pilih PoH --</option>
                            @foreach($pohs as $p)
                                <option value="{{ $p->name }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Lokasi Kerja</label>
                        <select name="lokasi" id="edit-lokasi" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach($sites as $s)
                                @php $val = "Site " . $s->code . " – " . $s->name; @endphp
                                <option value="{{ $val }}">{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Status Kontrak</label>
                        <input type="text" name="contract_status" id="edit-contract" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Tgl Bergabung</label>
                        <input type="date" name="join_date" id="edit-join-date" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Waktu Kerja</label>
                        <input type="text" name="working_hours" id="edit-hours" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Jadwal Kerja / Roster</label>
                        <input type="text" name="working_schedule" id="edit-schedule" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Footer: Kode Dokumen</label>
                        <input type="text" name="footer_code" id="edit-footer-code" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Footer: Versi / Tgl</label>
                        <input type="text" name="footer_version" id="edit-footer-version" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Footer: Teks Halaman</label>
                    <input type="text" name="footer_page_text" id="edit-footer-page" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Nama Penandatangan</label>
                        <input type="text" name="signer_name" id="edit-signer-name" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Jabatan Penandatangan</label>
                        <input type="text" name="signer_title" id="edit-signer-title" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="edit-gross" class="text-xs font-bold tracking-wider text-slate-500 uppercase">Gaji Pokok (Rp)</label>
                        <input type="number" name="gross" id="edit-gross" required min="0"
                            class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200 focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $ACCENT }}">
                    </div>
                    <div class="space-y-1.5">
                        <label for="edit-allowance" class="text-xs font-bold tracking-wider text-slate-500 uppercase">Site Allowance (Rp)</label>
                        <input type="number" name="allowance" id="edit-allowance" required min="0"
                            class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200 focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $ACCENT }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Uang Makan</label>
                        <input type="text" name="meals_allowance" id="edit-meals" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Lembur</label>
                        <input type="text" name="overtime" id="edit-overtime" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Pajak Penghasilan</label>
                        <input type="text" name="tax_borne_by" id="edit-tax" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Status Offer</label>
                        <select name="status" id="edit-status" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200 focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $ACCENT }}">
                            <option value="draft">DRAFT</option>
                            <option value="sent">SENT</option>
                            <option value="accepted">ACCEPTED</option>
                            <option value="rejected">REJECTED</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold tracking-wider text-slate-500 uppercase">Pengurangan Penghasilan (BPJS dll)</label>
                    <input type="text" name="deductions" id="edit-deductions" class="w-full px-4 py-2 text-sm border rounded-xl border-slate-200">
                </div>

                <div class="space-y-1.5">
                    <label for="edit-body" class="text-xs font-bold tracking-wider text-slate-500 uppercase">Pesan Email / Template</label>
                    <textarea name="body" id="edit-body" rows="6" required
                        class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-200 focus:outline-none focus:ring-2 min-h-[120px]" style="--tw-ring-color: {{ $ACCENT }}"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-50">
                    <button type="button" onclick="closeEditOfferModal()"
                        class="px-5 py-2.5 text-sm font-semibold transition bg-white border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white transition rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] shadow-md hover:brightness-105">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditOfferModal(offerId, candidateName, gross, allowance, body, status, meta, defCompany, defLevel, defPoh) {
            const modal = document.getElementById('modal-edit-offer');
            const form = document.getElementById('form-edit-offer');
            
            document.getElementById('edit-offer-title').textContent = 'Edit Offer — ' + candidateName;
            document.getElementById('edit-gross').value = gross;
            document.getElementById('edit-allowance').value = allowance;
            document.getElementById('edit-body').value = body;
            document.getElementById('edit-status').value = status;
            
            // Populate meta fields
            document.getElementById('edit-company').value = meta.company || defCompany || 'ANDALAN BHUMI NUSANTARA';
            document.getElementById('edit-doc-no').value = meta.doc_no || '';
            document.getElementById('edit-grade').value = meta.grade_level || defLevel || '';
            document.getElementById('edit-poh').value = meta.poh || defPoh || '';
            document.getElementById('edit-lokasi').value = meta.lokasi || '';
            document.getElementById('edit-contract').value = meta.contract_status || '';
            document.getElementById('edit-join-date').value = meta.join_date || '';
            document.getElementById('edit-hours').value = meta.working_hours || 'Senin – Minggu : Shift 1 & 2';
            document.getElementById('edit-schedule').value = meta.working_schedule || '<Roster Kerja>';
            document.getElementById('edit-meals').value = meta.meals_allowance || '&nbsp;';
            document.getElementById('edit-overtime').value = meta.overtime || 'Ditanggung Perusahaan';
            document.getElementById('edit-tax').value = meta.tax_borne_by || 'Ditanggung Perusahaan';
            document.getElementById('edit-deductions').value = meta.deductions || 'BPJS JHT 2% • BPJS JP 1% • BPJS Kesehatan 1%';
            document.getElementById('edit-signer-name').value = meta.signer_name || '';
            document.getElementById('edit-signer-title').value = meta.signer_title || '';
            document.getElementById('edit-footer-code').value = meta.footer_code || 'AAP-HRM-SDF-003';
            document.getElementById('edit-footer-version').value = meta.footer_version || 'v01/01/2022';
            document.getElementById('edit-footer-page').value = meta.footer_page_text || 'Page {PAGE_NUM} of {PAGE_COUNT}';
            
            form.action = `/admin/offers/${offerId}`;
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEditOfferModal() {
            const modal = document.getElementById('modal-edit-offer');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Close on ESC
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeEditOfferModal();
        });
    </script>
@endsection
