{{-- resources/views/admin/manpower/edit.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Atur Manpower'])

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
      <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Atur Manpower</h1>
      <p class="mt-1 text-sm text-slate-600">
        Job: <span class="font-medium">{{ $job->code ?? 'JOB' }}</span> — {{ $job->title }}
      </p>
      <div class="mt-3 text-xs text-slate-500">
        Openings saat ini:
        <span id="jobOpeningsCurrent" class="font-semibold text-slate-700">
          {{ number_format($job->openings) }}
        </span>
      </div>
    </div>
  </div>

  {{-- ===== Flash & Errors ===== --}}
  @if (session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3">
      <div class="font-medium mb-1">Periksa input berikut:</div>
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="grid gap-6 lg:grid-cols-3">
    {{-- ===== Form tambah/ubah baris ===== --}}
    <div class="card lg:col-span-1">
      <div class="card-body">
        <h2 class="font-semibold text-slate-900 mb-4">Tambah / Ubah Baris</h2>

        <form id="manpowerForm" method="POST" action="{{ route('admin.manpower.update', $job) }}" class="space-y-4">
          @csrf
          @method('PUT')
          {{-- Jika edit baris, isi row_id; jika create biarkan kosong --}}
          <input type="hidden" name="row_id" id="row_id" value="">

          {{-- Nama Aset (opsional) --}}
          <div>
            <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Nama Aset (opsional)</label>
            <input type="text" name="asset_name" id="asset_name" placeholder="mis. Dump Truck HD785 / Excavator PC200"
                   class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
          </div>

          {{-- Jumlah Aset --}}
          <div>
            <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Jumlah Aset</label>
            <input type="number" min="0" step="1" name="assets_count" id="assets_count" value="0"
                   class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
          </div>

          {{-- Rasio per Aset --}}
          <div>
            <div class="flex items-center justify-between">
              <label class="block text-[11px] uppercase tracking-wide text-slate-600 mb-1">Rasio per Aset</label>
              <div class="flex items-center gap-2">
                <button type="button" data-ratio="2.50"
                        class="px-2 py-1.5 rounded-lg border border-slate-200 text-xs hover:bg-slate-50">2.50</button>
                <button type="button" data-ratio="2.60"
                        class="px-2 py-1.5 rounded-lg border border-slate-200 text-xs hover:bg-slate-50">2.60</button>
              </div>
            </div>
            <input type="number" step="0.01" name="ratio_per_asset" id="ratio_per_asset" value="2.50"
                   class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
          </div>

          {{-- Actions --}}
          <div class="pt-2 flex items-center gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-white hover:bg-teal-700 active:bg-teal-800 shadow-sm">
              Simpan & Sinkron
            </button>
            <button type="button" id="btnReset"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
              Reset Form
            </button>
            <a href="{{ route('admin.dashboard.manpower') }}"
               class="ml-auto inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-700 hover:bg-slate-50">
              ← Kembali ke Dashboard
            </a>
          </div>
        </form>

        <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 p-3 text-xs text-slate-600">
          Rumus budget per baris: <code>ceil(assets_count × ratio_per_asset)</code>. Total openings job = jumlah semua baris.
        </div>
      </div>
    </div>

    {{-- ===== Tabel baris manpower ===== --}}
    <div class="card lg:col-span-2">
      <div class="card-body">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-slate-900">Baris Manpower (per Aset)</h2>
          <div class="text-xs text-slate-500">Klik “Edit” untuk memuat data ke form di kiri.</div>
        </div>

        @if ($rows->isEmpty())
          <div class="rounded-xl border border-dashed border-slate-300 p-10 text-center bg-white/50">
            <div class="text-slate-700 font-medium">Belum ada data manpower untuk job ini.</div>
            <div class="text-slate-500 text-sm mt-1">Tambahkan baris di form sebelah kiri lalu simpan.</div>
          </div>
        @else
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
              <thead class="bg-slate-50">
                <tr class="text-slate-600">
                  <th class="px-3 py-2 text-left font-medium">Aset</th>
                  <th class="px-3 py-2 text-right font-medium">Jumlah</th>
                  <th class="px-3 py-2 text-right font-medium">Rasio</th>
                  <th class="px-3 py-2 text-right font-medium">Budget</th>
                  <th class="px-3 py-2 text-right font-medium">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                @foreach ($rows as $r)
                  <tr class="hover:bg-slate-50/60">
                    <td class="px-3 py-2">{{ $r->asset_name ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format($r->assets_count) }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format($r->ratio_per_asset, 2) }}</td>
                    <td class="px-3 py-2 text-right font-semibold text-slate-900">{{ number_format($r->budget_headcount) }}</td>
                    <td class="px-3 py-2 text-right">
                      <div class="inline-flex items-center gap-2">
                        {{-- Tombol Edit: pakai data-* per field (tanpa @json) --}}
                        <button
                          class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs"
                          data-action="edit"
                          data-row-id="{{ $r->id }}"
                          data-asset-name="{{ $r->asset_name }}"
                          data-assets-count="{{ $r->assets_count }}"
                          data-ratio="{{ number_format($r->ratio_per_asset, 2, '.', '') }}"
                        >Edit</button>

                        <form action="{{ route('admin.manpower.destroy', [$job, $r]) }}" method="POST"
                              onsubmit="return confirm('Hapus baris ini? Tindakan ini akan memicu sinkron ulang openings.');">
                          @csrf
                          @method('DELETE')
                          <button type="submit"
                                  class="px-3 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50 text-xs">
                            Hapus
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- ===== JS: helper ratio + edit filler + submit AJAX untuk update openings langsung ===== --}}
  <script>
    (function(){
      const form       = document.getElementById('manpowerForm');
      const openingsEl = document.getElementById('jobOpeningsCurrent');

      // Quick ratio buttons
      const btnsRatio = form.querySelectorAll('button[data-ratio]');
      btnsRatio.forEach(b => b.addEventListener('click', () => {
        const input = document.getElementById('ratio_per_asset');
        input.value = b.dataset.ratio;
        input.focus(); input.select();
      }));

      // Handler tombol Edit (ambil dari data-* per field)
      document.querySelectorAll('button[data-action="edit"]').forEach(btn => {
        btn.addEventListener('click', () => {
          const d = btn.dataset;
          document.getElementById('row_id').value          = d.rowId || '';
          document.getElementById('asset_name').value      = d.assetName || '';
          document.getElementById('assets_count').value    = d.assetsCount ?? 0;
          document.getElementById('ratio_per_asset').value = d.ratio ? parseFloat(d.ratio).toFixed(2) : '2.50';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      });

      // Reset form
      document.getElementById('btnReset')?.addEventListener('click', () => {
        form.reset();
        document.getElementById('row_id').value = '';
        document.getElementById('ratio_per_asset').value = '2.50';
      });

      // ---- Submit via fetch agar openings langsung update tanpa reload ----
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form); // sudah ada _token & _method=PUT
        try {
          const res = await fetch(form.action, {
            method: 'POST', // Laravel akan membaca _method=PUT
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: fd
          });
          if (!res.ok) throw new Error('Gagal menyimpan. Periksa input.');

          const data = await res.json();

          // Update angka openings di header
          if (data?.job?.openings != null && openingsEl) {
            openingsEl.textContent = Number(data.job.openings).toLocaleString('id-ID');
          }

          // Kalau baris yang diupdate sudah ada di tabel — update nilai kolomnya
          const rowId = data?.row?.id;
          if (rowId) {
            const editBtn = document.querySelector(`button[data-action="edit"][data-row-id="${rowId}"]`);
            if (editBtn) {
              const tr = editBtn.closest('tr');
              if (tr) {
                const tds = tr.querySelectorAll('td');
                // Kolom: [0]=aset, [1]=jumlah, [2]=rasio, [3]=budget, [4]=aksi
                tds[0].textContent = data.row.asset_name ?? '—';
                tds[1].textContent = (data.row.assets_count ?? 0).toLocaleString('id-ID');
                tds[2].textContent = (Number(data.row.ratio_per_asset ?? 0).toFixed(2));
                tds[3].textContent = (data.row.budget_headcount ?? 0).toLocaleString('id-ID');

                // sync ulang data-* pada tombol edit
                editBtn.dataset.assetName   = data.row.asset_name ?? '';
                editBtn.dataset.assetsCount = data.row.assets_count ?? 0;
                editBtn.dataset.ratio       = Number(data.row.ratio_per_asset ?? 0).toFixed(2);
              }
            } else {
              // Baris baru: supaya tabel fresh, reload halaman
              location.reload();
              return;
            }
          }

          // Optional: reset form ke mode "tambah"
          form.reset();
          document.getElementById('row_id').value = '';
          document.getElementById('ratio_per_asset').value = '2.50';

        } catch (err) {
          alert(err.message || 'Terjadi kesalahan.');
        }
      });
    })();
  </script>
@endsection
