{{-- resources/views/offers/pdf.blade.php --}}
@php
    /** @var \App\Models\Offer $offer */
    $app = $offer->application;
    $user = $app?->user;
    $job = $app?->job;
    $site = $job?->site;

    $m = (array) ($offer->meta ?? []);

    $company = $m['company'] ?? ($job?->company?->name ?? 'ANDALAN BHUMI NUSANTARA'); 
    $footerCode = $m['footer_code'] ?? 'AAP-HRM-SDF-003';
    $footerVersion = $m['footer_version'] ?? 'v01/01/2022';
    $logoPath = public_path('assets/logo-abn.png');

    $docNo = $m['doc_no'] ?? '';
    $candidateNik = $m['candidate_nik'] ?? '';
    $gradeLevel = $m['grade_level'] ?? $m['level'] ?? ($job?->level_label ?? '');
    $poh = $m['poh'] ?? ($app?->poh?->name ?? '');
    $lokasiDisplay = $m['lokasi'] ?? (($site?->code ? 'Site ' . $site->code . ' – ' : '') . ($site?->name ?: 'Site HO – Head Office'));
    $joinDate = $m['join_date'] ?? null;
    $contractStatus = $m['contract_status'] ?? 'Perjanjian Kerja Waktu Tertentu (PKWT) masa kontrak 6 bulan dan direview sebelum berakhir.';
    $footerPageText = $m['footer_page_text'] ?? 'Page {PAGE_NUM} of {PAGE_COUNT}';
    $workingHours = $m['working_hours'] ?? 'Senin – Minggu : Shift 1 (06.00–18.00 WIB) & Shift 2 (18.00–06.00 WIB)';
    $workingSchedule = $m['working_schedule'] ?? $m['roster_kerja'] ?? '<Roster Kerja>';
    
    $overtimeRate = $m['overtime'] ?? $m['overtime_rate'] ?? 'Ditanggung Perusahaan';
    $mealsAllowance = $m['meals_allowance'] ?? '&nbsp;';
    $taxBorneBy = $m['tax_borne_by'] ?? 'Ditanggung Perusahaan';
    $bonusBulanan = $m['bonus_bulanan'] ?? 'Bonus diatur sesuai ketentuan perusahaan';

    // Penandatangan otomatis sesuai level
    $jobLevel = $job?->level ?? '';
    if ($jobLevel === 'non_staff') {
      $defSigner = 'Hendy Fardiansyah';
      $defTitle = 'Manager HRGA';
    } else {
      $defSigner = 'Roy Hansen C Saragih';
      $defTitle = 'General Manager';
    }
    
    $signerName = $m['signer_name'] ?? $defSigner;
    $signerTitle = $m['signer_title'] ?? $defTitle;
    $deptName = $m['signer_title'] ?? $defTitle;

    $signImage = $m['sign_image']
        ?? (is_file(storage_path('app/public/ttdmahya.png')) ? storage_path('app/public/ttdmahya.png')
            : (is_file(public_path('assets/sign_ceo.png')) ? public_path('assets/sign_ceo.png') : null));

    $gajiPokok = data_get($offer->salary, 'gross', 0);
    $insLap = data_get($offer->salary, 'allowance', 0);

    $candidateName = $user?->name ?: 'Calon Karyawan';
    $position = $job?->title ?: 'Plant Engineer';

    $today = now()->timezone(config('app.timezone', 'Asia/Jakarta'));
    $todayText = $today->translatedFormat('j F Y');
    $joinText = $joinDate ? \Illuminate\Support\Carbon::parse($joinDate)->translatedFormat('j F Y') : '';

    $fmt = fn($v) => filled($v) ? e($v) : '&nbsp;';
    $idr = function($n) {
        if (is_numeric($n) && $n > 0) return 'Rp. ' . number_format((float) $n, 0, ',', '.');
        if (is_numeric($n) && (float)$n === 0.0) return 'Rp. 0';
        return $n ?: '&nbsp;';
    };

    $bpjsText = $m['deductions'] ?? $m['bpjs_employee'] ?? 'BPJS JHT 2% sesuai ketentuan Pemerintah • BPJS Jaminan Pensiun 1% sesuai ketentuan Pemerintah • BPJS Kesehatan 1% sesuai ketentuan Pemerintah';
    $bpjsItems = preg_split('/\s*[•|]\s*/u', (string) $bpjsText, -1, PREG_SPLIT_NO_EMPTY);

    $benefit = [
        'Karyawan akan diikutsertakan dalam program BPJS Kesehatan dan BPJS Ketenagakerjaan.',
        'Perusahaan akan mengikutsertakan Karyawan, pasangan sah, dan maksimal 3 anak (di bawah 21 tahun) ke dalam program BPJS Kesehatan.',
        'THR akan dihitung prorata sesuai peraturan Undang-Undang Ketenagakerjaan.',
        'Bagi karyawan yang ditempatkan di site, maka transportasi dan akomodasi dalam rangka cuti lapangan (<i>field break</i>) diatur sesuai regulasi Perusahaan.',
        'Akomodasi : ' . ($m['akomodasi'] ?? '&lt;&lt;Akomodasi&gt;&gt;'),
    ];
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Offering Letter</title>
  <style>
    /* ============================================================
       TARGET: semua konten + TTD + footer muat di 1 halaman A4
       Margin diperkecil, font 8.5px, padding super compact
       ============================================================ */
    @page {
      size: A4;
      margin: 10mm 12mm 16mm 12mm;
    }

    * {
      box-sizing: border-box;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    body {
      margin: 0;
      font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
      font-size: 42px;
      line-height: 1.28;
      color: #111;
    }

    .page { width: 100%; }

    /* ===== HEADER ===== */
    .header-logo-wrap {
      text-align: center;
      margin-bottom: 0;
      margin-top: -8px;
    }
    .logo {
      height: 100px;
      width: auto;
      max-width: 400px;
      object-fit: contain;
      display: inline-block;
    }

    .header-meta {
      display: table;
      width: 100%;
      margin-bottom: 3px;
    }
    .header-meta-left  { display: table-cell; width: 33%; vertical-align: top; }
    .header-meta-center {
      display: table-cell; width: 34%;
      text-align: center; vertical-align: middle;
      font-size: 16px;
    }
    .header-meta-right { display: table-cell; width: 33%; }

    .doc-title {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      line-height: 1.15;
      color: #111;
    }
    .doc-private {
      font-size: 10px;
      font-weight: 800;
      color: #c0392b;
      line-height: 1.15;
    }

    /* Greeting */
    .greeting {
      margin: 0 0 3px 0;
      font-size: 9.5px;
      line-height: 1.3;
    }

    /* ===== MAIN TABLE ===== */
    table.sheet {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #111;
      font-size: 9.5px;
      margin-bottom: 0;
    }

    /* Section header */
    .sheet tr.sec-row td {
      font-weight: 800;
      text-transform: uppercase;
      font-size: 9.5px;
      padding: 2px 5px;
      border-top: 1px solid #111;
      border-bottom: 0;
      vertical-align: middle;
      letter-spacing: 0.1px;
    }

    /* Data rows — super compact */
    .sheet tr.data-row td {
      padding: 1px 5px;
      line-height: 1.28;
      vertical-align: top;
      border: 0;
      font-size: 9.5px;
    }
    .sheet tr.data-row.last td {
      padding-bottom: 2px;
    }

    .sheet td.key  { width: 140px; min-width: 120px; }
    .sheet td.sep  { width: 8px; text-align: center; padding-left: 0; padding-right: 0; }
    .sheet td.val  { }

    /* Full-width cells (benefit, others) */
    .sheet td.full {
      padding: 1px 6px 2px 6px;
      font-size: 9.5px;
      line-height: 1.28;
      vertical-align: top;
    }

    /* BPJS bullet list */
    .bpjs-list {
      margin: 0;
      padding-left: 14px;
      list-style: disc;
      line-height: 1.25;
    }
    .bpjs-list li { margin: 0; }

    /* Benefit list */
    .benefit-list {
      margin: 0;
      padding-left: 12px;
      list-style-type: lower-alpha;
      line-height: 1.28;
    }
    .benefit-list li { margin: 0; }

    /* Others list */
    .others-list {
      margin: 1px 0 1px 12px;
      padding: 0;
      line-height: 1.28;
      font-size: 9.5px;
    }
    .others-list li { margin-bottom: 1px; }

    /* ===== SIGNATURE BOX ===== */
    table.sigbox {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #111;
      margin-top: 4px;
      font-size: 9.5px;
    }
    .sigbox td {
      border: 1px solid #111;
      padding: 3px 6px;
      vertical-align: top;
      width: 50%;
    }
    .sigbox .sig-header {
      text-align: center;
      font-size: 9.5px;
      font-weight: normal;
      padding: 3px 6px 2px;
    }
    .sigbox .sig-area {
      height: 135px;
      padding: 2px 6px 2px;
      vertical-align: bottom;
      text-align: center;
    }
    .sig-sign {
      display: block;
      height: 95px;
      margin: 0 auto 2px;
      object-fit: contain;
    }
    .sig-space {
      display: block;
      height: 95px;
    }
    .sigbox .sig-name {
      font-weight: 800;
      text-transform: uppercase;
      font-size: 9.5px;
      text-align: center;
    }
    .sigbox .sig-title {
      font-size: 9px;
      color: #555;
      text-align: center;
    }
    .sigbox .sig-date {
      padding: 2px 6px 3px;
      font-size: 9.5px;
      vertical-align: top;
    }

    /* ===== FOOTER fixed ===== */
    .footer {
      position: fixed;
      left: 0; right: 0;
      bottom: -12mm;
      width: 100%;
    }
    .footer-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9px;
      color: #333;
      border-top: 0.5px solid #aaa;
    }
    .foot-left  { text-align: left;   padding: 1px 0; }
    .foot-mid   { text-align: center; padding: 1px 0; }
    .foot-right { text-align: right;  padding: 1px 0; }
  </style>
</head>
<body>

<div class="page">

  {{-- ===== HEADER ===== --}}
  {{-- Logo center --}}
  <div class="header-logo-wrap">
    <img class="logo" src="{{ $logoPath }}" alt="Logo Andalan">
  </div>

  {{-- Row: OFFERING LETTER left | No: center | empty right --}}
  <div class="header-meta">
    <div class="header-meta-left">
      <div class="doc-title">OFFERING LETTER</div>
      <div class="doc-private">PRIBADI &amp; RAHASIA</div>
    </div>
    <div class="header-meta-center">
      No : {!! $fmt($docNo) !!}
    </div>
    <div class="header-meta-right"></div>
  </div>

  {{-- Greeting --}}
  <p class="greeting">
    Dear Saudara/i <strong>{{ $candidateName }}</strong>@if(filled($candidateNik)), ({{ $candidateNik }})@endif<br>
    Dengan senang hati kami memberikan penawaran untuk bergabung dengan PT {!! $fmt($company) !!}, dengan ketentuan sbb :
  </p>

  {{-- ===== MAIN TABLE ===== --}}
  <table class="sheet">

    {{-- 1. JABATAN & TEMPAT PENERIMAAN --}}
    <tr class="sec-row"><td colspan="3">1. &nbsp; JABATAN &amp; TEMPAT PENERIMAAN</td></tr>
    <tr class="data-row"><td class="key">a.&nbsp; Jabatan</td><td class="sep">:</td><td class="val">{!! $fmt($position) !!}</td></tr>
    <tr class="data-row"><td class="key">b.&nbsp; Grade/Level</td><td class="sep">:</td><td class="val">{!! $fmt($gradeLevel) !!}</td></tr>
    <tr class="data-row last"><td class="key">c.&nbsp; Tempat Penerimaan (PoH)</td><td class="sep">:</td><td class="val">{!! $fmt($poh) !!}</td></tr>

    {{-- 2. LOKASI & STATUS KEKARYAWANAN --}}
    <tr class="sec-row"><td colspan="3">2. &nbsp; LOKASI &amp; STATUS KEKARYAWANAN</td></tr>
    <tr class="data-row"><td class="key">a.&nbsp; Lokasi</td><td class="sep">:</td><td class="val">{!! $fmt($lokasiDisplay) !!}</td></tr>
    <tr class="data-row"><td class="key">b.&nbsp; Status Perjanjian Kerja</td><td class="sep">:</td><td class="val">{!! $fmt($contractStatus) !!}</td></tr>
    <tr class="data-row last"><td class="key">c.&nbsp; Estimasi Tanggal Bergabung</td><td class="sep">:</td><td class="val">{!! $fmt($joinText) !!}</td></tr>

    {{-- 3. WAKTU KERJA & ISTIRAHAT --}}
    <tr class="sec-row"><td colspan="3">3. &nbsp; WAKTU KERJA &amp; ISTIRAHAT</td></tr>
    <tr class="data-row"><td class="key">a.&nbsp; Waktu Kerja</td><td class="sep">:</td><td class="val">{!! $fmt($workingHours) !!}</td></tr>
    <tr class="data-row"><td class="key">b.&nbsp; Jadwal Kerja</td><td class="sep">:</td><td class="val">{!! $fmt($workingSchedule) !!}</td></tr>
    <tr class="data-row last"><td class="key">c.&nbsp; Roster Kerja</td><td class="sep">:</td><td class="val">{!! $fmt($m['roster_kerja'] ?? '&lt;Roster Kerja&gt;') !!}</td></tr>

    {{-- 4. GAJI, BONUS, & PENGURANGAN PENGHASILAN --}}
    <tr class="sec-row"><td colspan="3">4. &nbsp; GAJI, BONUS, &amp; PENGURANGAN PENGHASILAN</td></tr>
    <tr class="data-row">
      <td class="key">a.&nbsp; Gaji Pokok</td>
      <td class="sep">:</td>
      <td class="val">
        <strong>{{ $idr($gajiPokok) }}</strong>
        @if(is_numeric($gajiPokok)) <span style="color:#555;">Gross/bulan</span> @endif
      </td>
    </tr>
    <tr class="data-row">
      <td class="key">b.&nbsp; Insentif / Site Allowance</td>
      <td class="sep">:</td>
      <td class="val">
        <strong>{{ $idr($insLap) }}</strong>
        @if(is_numeric($insLap)) <span style="color:#555;">Nett/hari</span> @endif
      </td>
    </tr>
    <tr class="data-row">
      <td class="key">c.&nbsp; Meals Allowance</td>
      <td class="sep">:</td>
      <td class="val">
        {!! $fmt($mealsAllowance) !!}
        @if(filled($mealsAllowance) && $mealsAllowance !== '&nbsp;')
          <span style="color:#555;">Nett/hari (diluar gaji, tanggal ditentukan Perusahaan)</span>
        @endif
      </td>
    </tr>
    <tr class="data-row"><td class="key">d.&nbsp; Overtime/Lembur</td><td class="sep">:</td><td class="val">{!! $fmt($overtimeRate) !!}</td></tr>
    <tr class="data-row"><td class="key">e.&nbsp; Pajak Penghasilan</td><td class="sep">:</td><td class="val">{!! $fmt($taxBorneBy) !!}</td></tr>
    <tr class="data-row last">
      <td class="key">f.&nbsp; Pengurangan Penghasilan</td>
      <td class="sep">:</td>
      <td class="val">
        <ul class="bpjs-list">
          @foreach ($bpjsItems as $it)
            <li>{{ trim($it) }}</li>
          @endforeach
        </ul>
      </td>
    </tr>

    {{-- 5. BENEFIT --}}
    <tr class="sec-row"><td colspan="3">5. &nbsp; BENEFIT</td></tr>
    <tr class="data-row last">
      <td class="full" colspan="3">
        <ol class="benefit-list" type="a" style="padding-left:14px; list-style-type:lower-alpha;">
          @foreach ($benefit as $b)
            <li>{!! $b !!}</li>
          @endforeach
        </ol>
      </td>
    </tr>

    {{-- 6. OTHERS --}}
    <tr class="sec-row"><td colspan="3">6. &nbsp; OTHERS</td></tr>
    <tr class="data-row">
      <td class="full" colspan="3">
        <ol class="others-list" type="a">
          <li>Jika Saudara menyetujui dan menerima Surat Penawaran Kerja (Offering Letter) ini, maka mohon di cantumkan tanggal bergabung dan silahkan tulisakan nama lengkap serta tanda tangan pada kolom yang telah disediakan</li>
          <li>Mohon untuk dapat mengirimkan kembali Surat Penawaran Kerja (Offering Letter) yang telah Saudara setujui kepada kami, paling lambat 2 hari setelah Surat Penawaran Kerja (Offering Letter) ini Saudara terima</li>
          <li>Surat Penawaran Kerja (Offering Letter) hanya berlaku jika calon karyawan dinyatakan <strong>Fit To Work</strong> pada Hasil MCU (Medical Check Up) dan atau Hasil Followup MCU dinyatakan <strong>Fit To Work</strong></li>
          <li>Untuk calon karyawan Operator dan Driver atau calon karyawan yang memerlukan Simper/Kimper Surat Penawaran Kerja (Offering Letter) hanya berlaku jika dinyatakan <strong>Lolos</strong> pada tahap <strong>Teori Test dan Ground Test</strong></li>
        </ol>
      </td>
    </tr>

  </table>

  {{-- ===== SIGNATURE BOX ===== --}}
  <table class="sigbox">
    <tr>
      <td class="sig-header">Penawaran oleh,<br>{{ $deptName }}</td>
      <td class="sig-header">Diterima dan disetujui oleh,<br>Calon Karyawan</td>
    </tr>
    <tr>
      <td class="sig-area">
        @if($signImage && is_file($signImage))
          <img src="{{ $signImage }}" class="sig-sign" alt="Tanda Tangan">
        @else
          <span class="sig-space"></span>
        @endif
        <div class="sig-name">{{ $signerName }}</div>
        <div class="sig-title">{{ $signerTitle }}</div>
      </td>
      <td class="sig-area">
        <span class="sig-space"></span>
        <div class="sig-name">{{ $candidateName }}</div>
      </td>
    </tr>
    <tr>
      <td class="sig-date">Tanggal : {!! $fmt($todayText) !!}</td>
      <td class="sig-date">Tanggal bergabung :</td>
    </tr>
  </table>

</div>

{{-- ===== FIXED FOOTER ===== --}}
<div class="footer">
  <table class="footer-table">
    <tr>
      <td class="foot-left">{{ $footerCode }}</td>
      <td class="foot-mid">Page 1 of 1</td>
      <td class="foot-right">{{ $footerVersion }}</td>
    </tr>
  </table>
</div>

<script type="text/php">
  if (isset($pdf)) {
    $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
    $font = $fontMetrics->get_font("DejaVu Sans", "normal");
    // $pdf->page_text(297, 810, $text, $font, 9, [0,0,0]);
  }
</script>

</body>
</html>