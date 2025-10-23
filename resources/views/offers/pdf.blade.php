{{-- resources/views/offers/pdf.blade.php --}}
@php
  /** @var \App\Models\Offer $offer */
  $app  = $offer->application;
  $user = $app?->user;
  $job  = $app?->job;
  $site = $job?->site;

  $m = (array) ($offer->meta ?? []);

  $company       = $m['company']       ?? 'ANDALAN BHUMI NUSANTARA'; // dipakai di paragraf saja
  $logoPath      = public_path('assets/logo-abn.png');

  $docNo         = $m['doc_no']        ?? '';
  $candidateNik  = $m['candidate_nik'] ?? '';
  $gradeLevel    = $m['level']         ?? '';
  $poh           = $m['poh']           ?? '';
  $lokasiDisplay = $m['lokasi']        ?? ( ($site?->code ? 'Site '.$site->code.' – ' : '').($site?->name ?: 'Site HO – Head Office') );
  $joinDate      = $m['join_date']     ?? null;
  $overtimeRate  = $m['overtime_rate'] ?? null;
  $bonusBulanan  = $m['bonus_bulanan'] ?? 'Bonus diatur sesuai ketentuan perusahaan';

  $signerName  = $m['signer_name']  ?? 'RAUL MAHYA KOMARAN';
  $signerTitle = $m['signer_title'] ?? 'General Manager';
  $deptName    = $m['dept_name']    ?? 'HR Department';

  // SIGNATURE: meta['sign_image'] > storage/app/public/ttdmahya.png > public/assets/sign_ceo.png
  $signImage = $m['sign_image']
               ?? (is_file(storage_path('app/public/ttdmahya.png')) ? storage_path('app/public/ttdmahya.png')
                  : (is_file(public_path('assets/sign_ceo.png')) ? public_path('assets/sign_ceo.png') : null));

  $gajiPokok = data_get($offer->salary, 'gross', 3912000);
  $insLap    = data_get($offer->salary, 'allowance', null);

  $candidateName = $user?->name ?: 'Calon Karyawan';
  $position      = $job?->title ?: 'Plant Engineer';

  $today     = now()->timezone(config('app.timezone','Asia/Jakarta'));
  $todayText = $today->translatedFormat('j F Y');
  $joinText  = $joinDate ? \Illuminate\Support\Carbon::parse($joinDate)->translatedFormat('j F Y') : '';

  $fmt = fn($v)=>filled($v)?e($v):'&nbsp;';
  $idr = fn($n)=>is_numeric($n)?'Rp. '.number_format((float)$n,0,',','.'):($n??'');

  // Pengurangan Penghasilan (dipisah " • ")
  $bpjsText  = $m['bpjs_employee'] ?? 'BPJS JHT 2% • BPJS JP 1% • BPJS Kesehatan 1% (sesuai ketentuan)';
  $bpjsItems = preg_split('/\s*•\s*/u', (string) $bpjsText, -1, PREG_SPLIT_NO_EMPTY);

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
    @page { size: A4; margin: 4px 16px 32px; } /* TOP makin kecil */

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
    .page{ width:86%; max-width:620px; margin:0 auto; }

    .right{ text-align:right; }
    .muted{ color:var(--mut); }
    .small{ font-size:12px; }

    /* ===== HEADER ===== */
    .hdr{
      display:flex; align-items:flex-start; gap:12px;
      margin-bottom:6px;
      margin-top:-25px;            /* tarik header lebih ke atas */
    }
    .brand{ flex:0 0 auto; text-align:center; margin-top:-8px; } /* ikut naik */
    .logo{
      height:180px;                /* lebih tinggi */
      width:300px;                 /* lebih lebar */
      max-width:300px;
      object-fit:contain;
      display:block;
      margin:0 auto 0;
      margin-top:-10px; 
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
      border-collapse:separate;
      border-spacing:0;
      border:1.6px solid var(--bd);
      margin-top:6px;
    }
    .sheet td{
      padding:2px 4px; vertical-align:top; border:0;
      line-height:1.28; word-spacing:1px;
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
    .footer{ position:fixed; left:0; right:0; bottom:6px; width:100%; }
    .footer-table{ width:100%; border-collapse:separate; border-spacing:0; font-size:12px; color:#111; }
    .foot-left{ text-align:left; } .foot-mid{ text-align:center; } .foot-right{ text-align:right; }
  </style>
</head>
<body>

  <div class="page">
    {{-- HEADER: logo kiri + nomor di bawah; teks di kanan --}}
    <div class="hdr">
      <div class="brand">
        <img class="logo" src="{{ $logoPath }}" alt="Logo" onerror="this.style.display='none'">
        {{-- Selalu tampilkan baris nomor (kalau kosong akan jadi "No : ") --}}
        <div class="no">No : {!! $fmt($docNo) !!}</div>
      </div>
      <div class="head">
        <div class="title small">OFFERING LETTER</div>
        <div class="priv small">PRIBADI &amp; RAHASIA</div>
      </div>
    </div>

    <p style="margin:0 0 4px">
      Dear Saudara/i <strong>{{ $fmt($candidateName) }}</strong>@if(filled($candidateNik)), ({{ $fmt($candidateNik) }})@endif —
      Dengan senang hati kami memberikan penawaran untuk bergabung dengan PT {{ $fmt($company) }} dengan ketentuan berikut:
    </p>

    {{-- Tabel utama (1–5) --}}
    <table class="sheet">
      <tr><td class="sec" colspan="3">1. Jabatan &amp; Tempat Penerimaan</td></tr>
      <tr><td class="key">a. Jabatan</td><td class="sep">:</td><td class="val">{!! $fmt($position) !!}</td></tr>
      <tr><td class="key">b. Grade/Level</td><td class="sep">:</td><td class="val">{!! $fmt($gradeLevel) !!}</td></tr>
      <tr><td class="key">c. Tempat Penerimaan (PoH)</td><td class="sep">:</td><td class="val">{!! $fmt($poh) !!}</td></tr>

      <tr><td class="sec" colspan="3">2. Lokasi &amp; Status Kekaryawanan</td></tr>
      <tr><td class="key">a. Lokasi</td><td class="sep">:</td><td class="val">{!! $fmt($lokasiDisplay) !!}</td></tr>
      <tr><td class="key">b. Status Perjanjian Kerja</td><td class="sep">:</td><td class="val">Perjanjian Kerja Waktu Tertentu (PKWT) masa kontrak 6 bulan dan direview sebelum berakhir.</td></tr>
      <tr><td class="key">c. Estimasi Tanggal Bergabung</td><td class="sep">:</td><td class="val">{!! $fmt($joinText) !!}</td></tr>

      <tr><td class="sec" colspan="3">3. Waktu Kerja &amp; Istirahat</td></tr>
      <tr><td class="key">a. Regular</td><td class="sep">:</td><td class="val">Senin – Minggu : Shift 1 (06.00–18.00 WIB) &amp; Shift 2 (18.00–06.00 WIB)</td></tr>
      <tr><td class="key">b. Istirahat</td><td class="sep">:</td><td class="val">Senin – Minggu : Shift 1 (12.00–13.00 WIB) &amp; Shift 2 (00.00–01.00 WIB)</td></tr>
      <tr><td class="key">c. Sistem Rotasi</td><td class="sep">:</td><td class="val">13 Hari Kerja : 1 Hari Libur</td></tr>
      <tr><td class="key">d. Roster Kerja</td><td class="sep">:</td><td class="val">12 Minggu On Site : 2 Minggu Field Break</td></tr>

      <tr><td class="sec" colspan="3">4. Gaji, Bonus, &amp; Pengurangan Penghasilan</td></tr>
      <tr><td class="key">a. Gaji Pokok</td><td class="sep">:</td><td class="val"><strong>{!! $fmt($idr($gajiPokok)) !!}</strong> <span class="muted">{{ $gajiPokok!==null ? 'Gross/bulan' : '' }}</span></td></tr>
      <tr><td class="key">b. Insentif Lapangan</td><td class="sep">:</td><td class="val">{!! $fmt($idr($insLap)) !!} <span class="muted">{{ $insLap!==null ? 'Nett/hari' : '' }}</span></td></tr>
      <tr><td class="key">c. Overtime</td><td class="sep">:</td><td class="val">{!! $fmt($overtimeRate ? $idr($overtimeRate).' Jam/hari' : '') !!}</td></tr>
      <tr><td class="key">d. Bonus Bulanan</td><td class="sep">:</td><td class="val">{!! $fmt($bonusBulanan) !!}</td></tr>
      <tr><td class="key">e. Pajak Penghasilan</td><td class="sep">:</td><td class="val">Ditanggung Perusahaan</td></tr>
      <tr>
        <td class="key">f. Pengurangan Penghasilan</td><td class="sep">:</td>
        <td class="val">
          <ul style="margin:0; padding-left:14px; list-style:disc; line-height:1.25;">
            @foreach ($bpjsItems as $it) <li>{{ trim($it) }}</li> @endforeach
          </ul>
        </td>
      </tr>

      @php
        $labels = ['a','b','c','d','e'];
        $benefitLines = [];
        foreach ($benefit as $i => $text) { $benefitLines[] = $labels[$i].'. '.e($text); }
      @endphp
      <tr><td class="sec no5-head" colspan="3">5. Benefit</td></tr>
      <tr><td class="no5-full" colspan="3"><div style="margin:1px 0 0 0; padding:0; line-height:1.15;">{!! implode('<br>', $benefitLines) !!}</div></td></tr>
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
        <td class="foot-left">AAP-HRM-SDF-003</td>
        <td class="foot-mid">Page {PAGE_NUM} of {PAGE_COUNT}</td>
        <td class="foot-right">v01/01/2022</td>
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
