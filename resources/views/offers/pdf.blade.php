{{-- resources/views/offers/pdf.blade.php --}}
@php
  /** @var \App\Models\Offer $offer */
  $app   = $offer->application;
  $user  = $app?->user;
  $job   = $app?->job;
  $site  = $job?->site;

  $meta           = (array) ($offer->meta ?? []);
  $docNo          = $meta['doc_no']        ?? '—';                        // 433/OL/HR/X/2025
  $candidateNik   = $meta['candidate_nik'] ?? null;                       // 6213...
  $level          = $meta['level']         ?? '—';                        // C3
  $poh            = $meta['poh']           ?? '—';                        // Banjarmasin, (Non Lokal)
  $lokasiDisplay  = $meta['lokasi']        // contoh isi lengkap seperti foto
                    ?? ( ($site?->code ? 'Site '.$site->code.' – ' : '') . ($site?->name ?: '—') );
  $joinDate       = $meta['join_date']     ?? null;                       // Y-m-d
  $overtimeRate   = $meta['overtime_rate'] ?? null;                       // angka
  $bonusBulanan   = $meta['bonus_bulanan'] ?? 'Bonus diatur sesuai ketentuan perusahaan';
  $company        = $meta['company']       ?? 'ANDALAN BHUMI NUSANTARA';
  $brandAbbr      = $meta['brand_abbr']    ?? 'ABN';

  $signerName     = $meta['signer_name']   ?? 'ROY HANSEN SABAGHI';
  $signerTitle    = $meta['signer_title']  ?? 'General Manager';
  $deptName       = $meta['dept_name']     ?? 'HR Department';

  $today          = now()->timezone(config('app.timezone','Asia/Jakarta'));
  $todayText      = $today->translatedFormat('j F Y');
  $joinText       = $joinDate ? \Illuminate\Support\Carbon::parse($joinDate)->translatedFormat('j F Y') : '—';

  // Komponen kompensasi
  $gajiPokok      = data_get($offer->salary, 'gross');
  $insLap         = data_get($offer->salary, 'allowance');

  $idr = fn($n) => is_numeric($n) ? 'Rp. '.number_format((float)$n,0,',','.') : '—';

  $candidateName  = $user?->name ?: '—';
  $position       = $job?->title ?: '—';
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Offering Letter</title>
  <style>
    @page { size: A4; margin: 28px 28px 36px; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#111; font-size:12px; line-height:1.55; }
    .topbar { display:flex; align-items:flex-start; gap:12px; }
    .logo { height:36px; }
    .brand { font-weight:900; font-size:26px; color:#d90416; letter-spacing:.4px; line-height:1; }
    .brand-sub { font-size:11px; color:#d90416; margin-top:2px; letter-spacing:.5px; }
    .right { margin-left:auto; text-align:right; }
    .small { font-size:11px; }
    .badge-red { color:#b91c1c; font-weight:700; }
    .muted { color:#6b7280; }
    .line { height:1px; background:#e5e7eb; margin:10px 0 14px; }
    h2 { font-size:13px; margin:12px 0 6px; }
    .grid { width:100%; border-collapse:collapse; }
    .grid td { padding:3px 6px; vertical-align:top; }
    .grid .key { width:240px; }
    .tbl { width:100%; border-collapse:collapse; margin-top:6px; }
    .tbl th,.tbl td { border:1px solid #d1d5db; padding:6px 8px; vertical-align:top; }
    .tbl th { background:#f3f4f6; text-align:left; }
    ol.alpha { padding-left:18px; margin:0; }
    .sign { display:flex; justify-content:space-between; margin-top:28px; }
    .sign .box { width:48%; }
    .foot { font-size:10px; color:#6b7280; margin-top:8px; }
  </style>
</head>
<body>

  {{-- HEADER --}}
  <div class="topbar">
    {{-- kalau ada file logo, aktifkan img di bawah dan hapus teks ABN --}}
    {{-- <img class="logo" src="{{ public_path('assets/logo-abn.png') }}" onerror="this.style.display='none'"> --}}
    <div>
      <div class="brand">{{ $brandAbbr }}</div>
      <div class="brand-sub">{{ $company }}</div>
    </div>
    <div class="right">
      <div class="small">OFFERING LETTER</div>
      <div class="badge-red small">PRIBADI & RAHASIA</div>
      <div class="small" style="margin-top:6px">No : {{ $docNo }}</div>
    </div>
  </div>
  <div class="line"></div>

  {{-- SALUTATION persis seperti foto --}}
  <p>
    Dear Saudara/i <strong>{{ $candidateName }}</strong>{{ $candidateNik ? ', ('.$candidateNik.')' : '' }}<br>
    Dengan senang hati kami memberikan penawaran untuk bergabung dengan PT {{ $company }}, dengan
    ketentuan sbb :
  </p>

  {{-- 1. JABATAN & TEMPAT PENERIMAAN --}}
  <h2>1. &nbsp; JABATAN &amp; TEMPAT PENERIMAAN</h2>
  <table class="grid">
    <tr><td class="key">a.&nbsp; Jabatan</td><td>: {{ $position }}</td></tr>
    <tr><td class="key">b.&nbsp; Grade/Level</td><td>: {{ $level }}</td></tr>
    <tr><td class="key">c.&nbsp; Tempat Penerimaan (PoH)</td><td>: {{ $poh }}</td></tr>
  </table>

  {{-- 2. LOKASI & STATUS KEKARYAWANAN --}}
  <h2>2. &nbsp; LOKASI &amp; STATUS KEKARYAWANAN</h2>
  <table class="grid">
    <tr><td class="key">a.&nbsp; Lokasi</td><td>: {{ $lokasiDisplay }}</td></tr>
    <tr>
      <td class="key">b.&nbsp; Status Perjanjian Kerja</td>
      <td>: Perjanjian Kerja Waktu Tertentu (PKWT) Masa Kontrak selama 6 bulan dan akan dilakukan review kontrak sebelum kontrak kerja berakhir.</td>
    </tr>
    <tr><td class="key">c.&nbsp; Estimasi Tanggal Bergabung</td><td>: {{ $joinText }}</td></tr>
  </table>

  {{-- 3. WAKTU KERJA & ISTIRAHAT --}}
  <h2>3. &nbsp; WAKTU KERJA &amp; ISTIRAHAT</h2>
  <table class="grid">
    <tr>
      <td class="key">a.&nbsp; Regular</td>
      <td>: Senin – Minggu : Shift 1 (06.00 s/d 18.00 WIB) &amp; Shift 2 (18.00 s/d 06.00 WIB)</td>
    </tr>
    <tr>
      <td class="key">b.&nbsp; Istirahat</td>
      <td>: Senin – Minggu : Shift 1 (12.00 s/d 13.00 WIB) &amp; Shift 2 (00.00 s/d 01.00 WIB)</td>
    </tr>
    <tr>
      <td class="key">c.&nbsp; Sistem Rotasi</td>
      <td>: 13 Hari Kerja : 1 Hari Libur</td>
    </tr>
    <tr>
      <td class="key">d.&nbsp; Roster Kerja</td>
      <td>: 12 Minggu On Site : 2 Minggu Field Break</td>
    </tr>
  </table>

  {{-- 4. GAJI, BONUS, & PENGURANGAN PENGHASILAN --}}
  <h2>4. &nbsp; GAJI, BONUS, &amp; PENGURANGAN PENGHASILAN</h2>
  <table class="tbl">
    <tr>
      <th style="width:42%">Komponen</th>
      <th>Keterangan</th>
    </tr>
    <tr>
      <td>a.&nbsp; Gaji Pokok</td>
      <td>{{ $idr($gajiPokok) }} <span class="muted">/ Gross/bulan</span></td>
    </tr>
    <tr>
      <td>b.&nbsp; Insentif Lapangan</td>
      <td>{{ $idr($insLap) }} <span class="muted">/ per hari</span></td>
    </tr>
    <tr>
      <td>c.&nbsp; Overtime</td>
      <td>{{ $overtimeRate ? $idr($overtimeRate).' / jam' : '—' }}</td>
    </tr>
    <tr>
      <td>d.&nbsp; Bonus Bulanan</td>
      <td>{{ $bonusBulanan }}</td>
    </tr>
    <tr>
      <td>e.&nbsp; Pajak Penghasilan</td>
      <td>Ditanggung Perusahaan</td>
    </tr>
    <tr>
      <td>f.&nbsp; Pengurangan Penghasilan</td>
      <td>
        • BPJS JHT 2% sesuai ketentuan Pemerintah<br>
        • BPJS Jaminan Pensiun 1% sesuai ketentuan Pemerintah<br>
        • BPJS Kesehatan 1% sesuai ketentuan Pemerintah
      </td>
    </tr>
  </table>

  {{-- 5. BENEFIT --}}
  <h2>5. &nbsp; BENEFIT</h2>
  <ol class="alpha">
    <li>Karyawan akan diikutsertakan dalam program BPJS Kesehatan dan BPJS Ketenagakerjaan.</li>
    <li>Perusahaan akan mengikutsertakan Karyawan, pasangan, dan maksimal 3 anak (di bawah 21 tahun) ke dalam program BPJS Kesehatan.</li>
    <li>THR akan dibayarkan prorata sesuai peraturan Undang-Undang Ketenagakerjaan.</li>
    <li>Transportasi dan akomodasi dalam rangka cuti lapangan (field break) diatur sesuai regulasi Perusahaan.</li>
    <li>Akomodasi: Mess, Laundry, Catering 3x.</li>
  </ol>

  {{-- TANDA TANGAN --}}
  <div class="sign">
    <div class="box">
      <div>Penawaran oleh,<br>{{ $deptName }}</div><br><br><br>
      <div style="text-transform:uppercase; font-weight:700">{{ $signerName }}</div>
      <div class="muted">{{ $signerTitle }}</div>
    </div>
    <div class="box">
      <div>Disetujui oleh,<br>Calon Karyawan</div><br><br><br>
      <div style="text-transform:uppercase; font-weight:700">{{ $candidateName }}</div>
    </div>
  </div>

  <table class="grid" style="margin-top:10px">
    <tr><td class="key">Tanggal :</td><td>{{ $todayText }}</td></tr>
    <tr><td class="key">Tanggal bergabung :</td><td>{{ $joinText }}</td></tr>
  </table>

  <div class="foot">AAP-HRM-SDF-003 &nbsp; • &nbsp; Page 1 of 1 &nbsp; • &nbsp; v01/01/2022</div>

</body>
</html>
