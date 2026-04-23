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
    $deptName = $m['signer_title'] ?? $defTitle; // Gunakan jabatan sebagai penawaran oleh

    // SIGNATURE: meta['sign_image'] > storage/app/public/ttdmahya.png > public/assets/sign_ceo.png
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

    // Pengurangan Penghasilan (dipisah " • ")
    $bpjsText = $m['deductions'] ?? $m['bpjs_employee'] ?? 'BPJS JHT 2% • BPJS JP 1% • BPJS Kesehatan 1% (sesuai ketentuan)';
    $bpjsItems = preg_split('/\s*[•|]\s*/u', (string) $bpjsText, -1, PREG_SPLIT_NO_EMPTY);

    // Benefit
    $benefit = [
        'BPJS Kesehatan & BPJS Ketenagakerjaan.',
        'BPJS Kesehatan untuk karyawan, pasangan, & maks. 3 anak (<21 th).',
        'THR prorata sesuai peraturan perundang-undangan.',
        'Transport & akomodasi saat field break mengikuti regulasi perusahaan.',
        'Akomodasi: Mess, Laundry, Catering 3x.',
    ];
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Offering Letter</title>
  <style>
    /* Ganti ukuran kertas ke Legal (8.5in x 14in) agar lebih lebar */
    @page { size: 8.5in 14in; margin: 4px 16px 32px; }

    *{ box-sizing:border-box; -webkit-print-color-adjust:exact; print-color-adjust:exact; }

    :root{
      --fs: 11px;
      --lh: 1.36;
      --mut:#6b7280;
      --bd:#111;
    }
    body{
      margin:0; /* hilangkan margin default body */
      font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
      font-size:var(--fs);
      line-height:var(--lh);
      color:#111;
      word-spacing:1px;
    }

    /* Konten center */
    .page{ width:98%; max-width:900px; margin:0 auto; margin-top:-18px; }

    .right{ text-align:right; }
    .muted{ color:var(--mut); }
    .small{ font-size:12px; }

    /* ===== HEADER ===== */
    .hdr{
      display:flex; align-items:flex-start; gap:12px;
      margin-bottom:6px;
      margin-top:0;                /* normal, tidak terlalu ke atas */
    }
    .brand{ flex:0 0 auto; text-align:center; margin-top:0; } /* dinaikkan */
    .logo{
      height:110px;
      width:auto;
      max-width:320px; /* jauh lebih besar */
      object-fit:contain;
      display:block;
      margin:0 auto;
    }
    .no{
      font-size:12px; line-height:1;
      margin-top:-68px;            /* nempel di bawah logo */
    }

    .head{ flex:1 1 auto; text-align:left; padding-top:2px;margin-top:-65px;   }
    .head .title{ font-weight:800; letter-spacing:.4px; }
    .head .priv{ color:#b91c1c; font-weight:800; margin-top:2px; }

    /* ===== Tabel utama ===== */
    table.sheet{
      width:100%;
      max-width:880px;
      margin-left:auto;
      margin-right:auto;
      border-collapse:separate;
      border-spacing:0;
      border:2.2px solid var(--bd); /* lebih tebal */
      /* border-radius:8px; sudut membulat dihapus */
      margin-top:8px;
      background:#fff;
      box-shadow:0 1px 4px 0 #ddd;
    }
    .sheet td{
      padding:6px 10px; /* lebih lega */
      vertical-align:top;
      border:0;
      line-height:1.32;
      word-spacing:1px;
    }
    .sheet .sec{
      font-weight:800;
      text-transform:uppercase;
      padding:6px 12px;
    }
    .sheet .sec{ font-weight:800; text-transform:uppercase; padding:2px 6px; }
    .key{ width:200px; }
    .sep{ width:8px; text-align:center; }
    .val{ width:auto; }

    /* Benefit ringkas */
    .sheet td.no5-full{ padding:1px 6px !important; line-height:1.15 !important; }
    .sheet td.sec.no5-head{ padding:2px 6px !important; line-height:1.15 !important; }

    /* ===== TTD ===== */
    table.sigbox{
      width:100%; border-collapse:separate; border-spacing:0;
      border:1.6px solid var(--bd); margin-top: calc(var(--fs) * 3);
    }
    .sigbox td{ border:1px solid var(--bd); padding:6px 8px; vertical-align:top; word-spacing:1px; }
    .sigbox .area{ height:90px; vertical-align:middle; }
    .sigbox .center{ text-align:center; }
    .sigbox .meta{ color:var(--mut); font-size:10px; }
    .sig-sign{ display:block; height:50px; margin:2px auto 4px; object-fit:contain; }
    .sig-space{ display:block; height:50px; margin:2px auto 4px; }
    .sigbox .hcell{ text-align:center; font-size:14px; line-height:1.25; font-weight:600; }

    /* ===== FOOTER ===== */
    .footer{ position:fixed; left:0; right:0; bottom:0; width:100%; }
    .footer-table{ width:100%; border-collapse:separate; border-spacing:0; font-size:12px; color:#111; }
    .foot-left{ text-align:left; } .foot-mid{ text-align:center; } .foot-right{ text-align:right; }
  </style>
</head>
<body>

  <div class="page">
    {{-- HEADER: logo kiri + nomor di bawah; teks di kanan --}}
    <!-- HEADER: Logo tengah atas, judul kiri, nomor kanan -->
    <div style="width:100%; margin-bottom:8px; margin-top:-12px;">
      <div style="width:100%; text-align:center;">
        <img class="logo" src="{{ $logoPath }}" alt="Logo">
      </div>
      <div style="width:100%; display:flex; flex-direction:row; justify-content:space-between; align-items:flex-start; margin-top:-10px;">
        <div style="flex:1; text-align:left;">
          <div class="title small" style="font-weight:800; letter-spacing:.4px; color:#111; margin-top:-6px;">OFFERING LETTER</div>
          <div class="priv small" style="color:#b91c1c; font-weight:800; margin-top:0;">PRIBADI &amp; RAHASIA</div>
        </div>
        <div style="flex:1; text-align:center; font-size:12px; line-height:1; margin-top:-6px;">
          No : {!! $fmt($docNo) !!}
        </div>
        <div style="flex:1;"></div>
      </div>
    </div>

    <p style="margin:0 0 4px">
      Dear Saudara/i <strong>{{ $fmt($candidateName) }}</strong>@if(filled($candidateNik)), ({{ $fmt($candidateNik) }})@endif —<br>
      Dengan senang hati kami memberikan penawaran untuk bergabung dengan PT {{ $fmt($company) }} dengan ketentuan berikut:
    </p>

    <table class="sheet">
      <tr><td class="sec" colspan="3">1. JABATAN & TEMPAT PENERIMAAN</td></tr>
      <tr><td class="key">a. Jabatan</td><td class="sep">:</td><td class="val">{{ $fmt($position) }}</td></tr>
      <tr><td class="key">b. Grade/Level</td><td class="sep">:</td><td class="val">{{ $fmt($gradeLevel) }}</td></tr>
      <tr><td class="key">c. Tempat Penerimaan (PoH)</td><td class="sep">:</td><td class="val">{{ $fmt($poh) }}</td></tr>

      <tr><td class="sec" colspan="3">2. LOKASI & STATUS KEKARYAWANAN</td></tr>
      <tr><td class="key">a. Lokasi</td><td class="sep">:</td><td class="val">{{ $fmt($lokasiDisplay) }}</td></tr>
      <tr><td class="key">b. Status Perjanjian Kerja</td><td class="sep">:</td><td class="val">{{ $fmt($contractStatus) }}</td></tr>
      <tr><td class="key">c. Estimasi Tanggal Bergabung</td><td class="sep">:</td><td class="val">{{ $fmt($joinText) }}</td></tr>

      <tr><td class="sec" colspan="3">3. WAKTU KERJA & ISTIRAHAT</td></tr>
      <tr><td class="key">a. Waktu Kerja</td><td class="sep">:</td><td class="val">{{ $fmt($workingHours) }}</td></tr>
      <tr><td class="key">b. Jadwal Kerja</td><td class="sep">:</td><td class="val">{{ $fmt($workingSchedule) }}</td></tr>

      <tr><td class="sec" colspan="3">4. GAJI, BONUS, & PENGURANGAN PENGHASILAN</td></tr>
      <tr><td class="key">a. Gaji Pokok</td><td class="sep">:</td><td class="val"><strong>{{ $fmt($idr($gajiPokok)) }}</strong> <span class="muted">{{ is_numeric($gajiPokok) ? 'Gross/bulan' : '' }}</span></td></tr>
      <tr><td class="key">b. Insentif / Site Allowance</td><td class="sep">:</td><td class="val">{{ $fmt($idr($insLap)) }} <span class="muted">{{ is_numeric($insLap) ? 'Nett/hari' : '' }}</span></td></tr>
      <tr><td class="key">c. Uang Makan</td><td class="sep">:</td><td class="val">{{ $fmt($mealsAllowance) }}</td></tr>
      <tr><td class="key">d. Overtime/Lembur</td><td class="sep">:</td><td class="val">{{ $fmt($overtimeRate) }}</td></tr>
      <tr><td class="key">e. Pajak Penghasilan</td><td class="sep">:</td><td class="val">{{ $fmt($taxBorneBy) }}</td></tr>
      <tr><td class="key">f. Pengurangan Penghasilan</td><td class="sep">:</td><td class="val">
        <ul style="margin:0; padding-left:14px; list-style:disc; line-height:1.25;">
          @foreach ($bpjsItems as $it) <li>{{ trim($it) }}</li> @endforeach
        </ul>
      </td></tr>

      @php
        $labels = ['a', 'b', 'c', 'd', 'e'];
        $benefitLines = [];
        foreach ($benefit as $i => $text) {
            $benefitLines[] = $labels[$i] . '. ' . e($text);
        }
      @endphp
      <tr><td class="sec no5-head" colspan="3">5. BENEFIT</td></tr>
      <tr><td class="no5-full" colspan="3"><div style="margin:1px 0 0 0; padding:0; line-height:1.15;">{!! implode('<br>', $benefitLines) !!}</div></td></tr>

      <tr><td class="sec" colspan="3">6. OTHERS</td></tr>
      <tr>
        <td colspan="3" style="font-size:11px; line-height:1.4; padding:8px 18px;">
          <ol type="a" style="margin:0 0 0 18px; padding:0 0 0 0;">
            <li>Jika Saudara menyetujui dan menerima Surat Penawaran Kerja (Offering Letter) ini, maka mohon di cantumkan tanggal bergabung dan silahkan tuliskan nama lengkap serta tanda tangan pada kolom yang telah disediakan.</li>
            <li>Mohon untuk dapat mengirimkan kembali Surat Penawaran Kerja (Offering Letter) yang telah Saudara setujui kepada kami, paling lambat 2 hari setelah Surat Penawaran Kerja (Offering Letter) ini Saudara terima.</li>
            <li>Surat Penawaran Kerja (Offering Letter) hanya berlaku jika calon karyawan dinyatakan <b>Fit To Work</b> pada Hasil MCU (Medical Check Up) dan atau Hasil <b>Soliuog MCU</b> dinyatakan <b>Fit To Work</b>.</li>
            <li>Apabila Saudara tidak mengembalikan Surat Penawaran Kerja (Offering Letter) dalam waktu yang telah ditentukan, maka penawaran ini dianggap batal.</li>
          </ol>
        </td>
      </tr>
    </table>

    {{-- TTD --}}
    <table class="sigbox">
      <tr><td class="hcell">Penawaran oleh,<br>{{ $deptName }}</td><td class="hcell">Disetujui oleh,<br>Calon Karyawan</td></tr>
      <tr>
        <td class="area center">
          @if($signImage && is_file($signImage))
            <img src="{{ $signImage }}" class="sig-sign" alt="ttdmahya" onerror="this.style.display='none'">
          @else <span class="sig-space"></span> @endif
          <div><strong style="text-transform:uppercase">{{ $fmt($signerName) }}</strong></div>
          <div class="meta">{{ $fmt($signerTitle) }}</div>
        </td>
        <td class="area center">
          <span class="sig-space"></span>
          <div><strong style="text-transform:uppercase">{{ $fmt($candidateName) }}</strong></div>
        </td>
      </tr>
      <tr><td> Tanggal : {!! $fmt($todayText) !!}</td><td> Tanggal bergabung : {!! $fmt($joinText) !!}</td></tr>
    </table>
  </div>

  {{-- Footer tetap bawah-tengah --}}
  <div class="footer">
    <table class="footer-table">
      <tr>
        <td class="foot-left">{{ $footerCode }}</td>
        <td class="foot-mid">{{ str_replace(['{PAGE_NUM}', '{PAGE_COUNT}'], ['{PAGE_NUM}', '{PAGE_COUNT}'], $footerPageText) }}</td>
        <td class="foot-right">{{ $footerVersion }}</td>
      </tr>
    </table>
  </div>

  <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->get_font("DejaVu Sans","normal");
        // $pdf->page_text(297, 810, $text, $font, 9, array(0,0,0));
    }
  </script>
</body>
</html>
