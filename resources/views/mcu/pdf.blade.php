<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Undangan MCU</title>
    <style>
        @page { margin: 30px 50px 80px 50px; }
        body { font-family: 'Poppins', Arial, sans-serif; font-size: 11pt; color: #000; line-height: 1.44; }

        /* ── HEADER LOGO ── */
        .header-logo { margin-bottom: 20px; }
        .logo-img { height: 60px; }
        /* Fallback teks logo jika gambar tidak tersedia */
        .logo-text {
            font-size: 26pt;
            font-weight: bold;
            color: #5a3e28; /* coklat seperti di PDF */
            letter-spacing: 2px;
        }

        /* ── BARIS ATAS: tanggal+nomor (kiri) & kotak project (kanan) ── */
        .top-info { overflow: hidden; margin-bottom: 20px; }
        .top-left  { float: left;  width: 60%; }
        .top-right { float: right; width: 35%; text-align: right; }

        .project-box {
            display: inline-block;
            border: 2px solid red;
            padding: 4px 12px;
            font-weight: bold;
            font-size: 12pt;
        }
        .clear { clear: both; }

        /* ── PENERIMA ── */
        .recipient { margin-bottom: 20px; }

        /* ── HAL / SUBJECT ── */
        .subject { font-weight: bold; text-decoration: underline; margin-bottom: 15px; }

        /* ── ISI SURAT ── */
        .content { margin-bottom: 15px; text-align: justify; }

        /* ── TABEL DATA KARYAWAN ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }
        .data-table th {
            background-color: #d1d5db;
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
        }
        .data-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
        }

        /* ── CATATAN ── */
        .notes { margin-top: 15px; }
        .notes-title { font-weight: bold; margin-bottom: 4px; font-size: 11pt; }
        .notes-list {
            margin: 0 0 0 20px;
            padding: 0;
            list-style: decimal;
            font-style: italic; /* italic sesuai PDF */
        }
        .notes-list li { margin-bottom: 3px; }

        /* ── EMAIL ── */
        .email-info { margin-top: 15px; }
        .email-list {
            margin: 5px 0 0 30px;
            padding: 0;
            list-style: disc;
        }
        .email-list li { color: #1155cc; text-decoration: underline; margin-bottom: 2px; }

        /* ── TANDA TANGAN ── */
        .footer-sign { margin-top: 30px; }
        .signature-space { height: 70px; } /* ruang untuk tanda tangan fisik */
        .signer-name  { font-weight: bold; text-decoration: underline; margin-bottom: 0; }
        .signer-title { margin-top: 2px; }

        /* ── FOOTER HALAMAN (fixed bottom) ── */
       .page-footer {
    position: fixed;
    bottom: -85px; /* garis tetap bawah */
    left: 0;
    right: 0;
    text-align: right;
    font-size: 8pt;
    background: transparent;
    padding-right: 12px;
}

.page-footer .footer-top {
    position: relative;
    top: -35px; /* kurangin naiknya */
    line-height: 1.5; /* spacing 1 */
}

.footer-bar {
    width: calc(100% + 85px);
    height: 28px;
    margin-right: -85px;
    background: #dfead0;
    border-radius: 8px;
}
        .footer-company {   
            font-weight: bold;
            margin-bottom: 2px;
            color: #222; /* teks gelap seperti pada draft */
            font-size: 17pt;
        }
        .footer-detail {
            color: #222;
            line-height: 1.3;
            font-size: 13pt;
            font-weight: 500;
        }

        /* Rounded inset green bar to mimic admin draft */
        .footer-bar {
            width: calc(100% + 85px); /* dipanjangkan ke kanan */
            height: 28px;
            margin: 0;
            margin-right: -55px; /* stretch ke kanan penuh */
            background: #dfead0; /* pale green fill, tanpa dark band */
            border-radius: 8px; /* rounded semua corner */
            box-shadow: none;
        }
        /* make sure small PDFs keep consistent sizing */
        @media print {
            .footer-bar { height: 22px; border-top-width: 8px; }
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('assets/logo-abn.png');
    @endphp

    <!-- ══ LOGO ══ -->
    <div class="header-logo">
        <img src="{{ $logoPath }}" class="logo-img" alt="Logo">
    </div>

    <!-- ══ BARIS ATAS ══ -->
    <div class="top-info">
        <div class="top-left">
            {{ $mcu_meta['city'] ?? 'Jakarta' }},
            {{ \Carbon\Carbon::parse($mcu_meta['doc_date'] ?? now())->translatedFormat('d F Y') }}<br>
            No.&nbsp;: {{ $mcu_meta['doc_no'] ?? '&lt;&lt;Nomor Surat&gt;&gt;' }}
        </div>
        <div class="top-right">
            <div class="project-box">
                {{ $mcu_meta['project_name'] ?? '&lt;&lt;Project&gt;&gt;' }} PROJECT
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- ══ PENERIMA ══ -->
    <div class="recipient">
        Kepada Yth.<br>
        <strong>{{ $mcu_meta['clinic_name'] ?? '&lt;&lt;Vendor&gt;&gt;' }}</strong><br>
        {{ $mcu_meta['clinic_address'] ?? '&lt;&lt;Alamat Lengkap Vendor MCU&gt;&gt;' }}
    </div>

    <!-- ══ HAL ══ -->
    <div class="subject">
        Hal: {{ $mcu_meta['subject'] ?? 'Medical Check Up – Pre Employee' }}
    </div>

    <p>Dengan Hormat,</p>

    <!-- ══ ISI ══ -->
    <div class="content">
        Sehubungan dengan program pelaksanaan <em>MCU ({{ $mcu_meta['for_text'] ?? '&lt;&lt;for&gt;&gt;' }})</em>
        PT. {{ $mcu_meta['bu_name'] ?? '&lt;&lt;BU&gt;&gt;' }} (AAP Group), bersama ini kami mengirimkan
        karyawan kami untuk dilakukan pemeriksaan kesehatan awal sesuai dengan jenis pemeriksaan yang telah
        ditentukan oleh Matrix MCU PT {{ $mcu_meta['matrix_owner'] ?? '&lt;&lt;Owner&gt;&gt;' }}
        kepada nama sebagai berikut:
    </div>

    <!-- ══ TABEL ══ -->
    <table class="data-table">
        <thead>
            <tr>
                <th>NIK</th>
                <th>Name</th>
                <th>Position</th>
                <th>Date Of<br>Born</th>
                <th>Age</th>
                <th>MCU Date</th>
                <th>Project<br>Code</th>
                <th>Package</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $application->user->id_employe ?? '&lt;&lt;NIK&gt;&gt;' }}</td>
                <td>{{ $application->user->name ?? '&lt;&lt;Nama&gt;&gt;' }}</td>
                <td>{{ $application->job->title ?? '&lt;&lt;Position&gt;&gt;' }}</td>
                <td>
                    {{ $application->user->candidateProfile->dob
                        ? \Carbon\Carbon::parse($application->user->candidateProfile->dob)->format('d/m/Y')
                        : '&lt;&lt;Date Of Born&gt;&gt;' }}
                </td>
                <td>
                    {{ $application->user->candidateProfile->dob
                        ? \Carbon\Carbon::parse($application->user->candidateProfile->dob)->age
                        : '&lt;&lt;Age&gt;&gt;' }}
                </td>
                <td>{{ \Carbon\Carbon::parse($mcu_meta['mcu_date'] ?? now())->format('d/m/Y') }}</td>
                <td>{{ $application->job->site->code ?? '&lt;&lt;Project Code&gt;&gt;' }}</td>
                <td>{{ $mcu_meta['package'] ?? '&lt;&lt;Package&gt;&gt;' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- ══ CATATAN ══ -->
    <div class="notes">
        <div class="notes-title">Catatan:</div>
        <ol class="notes-list">
            <li><em>Bagi kandidat berusia &gt; 40 tahun, diwajibkan menjalani pemeriksaan treadmill.</em></li>
            <li><em>Mohon cocokan KTP asli dengan identitas kandidat yang akan diperiksa.</em></li>
            @if(!empty($mcu_meta['extra_notes']))
                @foreach((array)$mcu_meta['extra_notes'] as $note)
                    <li><em>{{ $note }}</em></li>
                @endforeach
            @endif
        </ol>
    </div>

    <!-- ══ EMAIL ══ -->
    <div class="email-info">
        Hasil MCU harap dikirimkan ke email berikut:
        <ul class="email-list">
            {{-- Email tetap (sesuai PDF) --}}
            <li>bagusprasojo@pt-aap.com</li>
            <li>hendy.fardiansyah@pt-aap.com</li>
            <li>vidya.paramitha.putri@pt-aap.com</li>
            <li>rizal.abu@pt-aap.com</li>

            {{-- Email site dinamis (sesuai placeholder di PDF: <<Email Site 1>> s/d <<Email Site 5>>) --}}
            @php
                $site_emails = array_filter([
                    $mcu_meta['email_site_1'] ?? null,
                    $mcu_meta['email_site_2'] ?? null,
                    $mcu_meta['email_site_3'] ?? null,
                    $mcu_meta['email_site_4'] ?? null,
                    $mcu_meta['email_site_5'] ?? null,
                ]);
            @endphp
            @foreach($site_emails as $email)
                <li>{{ trim($email) }}</li>
            @endforeach

            {{-- Fallback: jika menggunakan key lama result_emails (multi-line string) --}}
            @if(empty($site_emails) && !empty($mcu_meta['result_emails']))
                @foreach(preg_split('/\r\n|\r|\n/', $mcu_meta['result_emails']) as $email)
                    @if(trim($email))
                        <li>{{ trim($email) }}</li>
                    @endif
                @endforeach
            @endif
        </ul>
    </div>

    <!-- ══ TANDA TANGAN ══ -->
    <div class="footer-sign">
        Hormat Kami<br>
        <strong>{{ $mcu_meta['footer_company_name'] ?? 'Andalan Artha Primanusa' }}</strong>

        <div class="signature-space">
            {{-- Ruang tanda tangan. Ganti dengan <img> jika ada file tanda tangan digital. --}}
            @if(isset($mcu_meta['signature_path']) && $mcu_meta['signature_path'])
                <img src="{{ $mcu_meta['signature_path'] }}" style="height:60px; margin-top:8px;" alt="ttd">
            @endif
        </div>

        <div class="signer-name">{{ $mcu_meta['signer_name'] ?? 'Roy Hansen C. Saragih' }}</div>
        <div class="signer-title">{{ $mcu_meta['signer_title'] ?? 'General Manager' }}</div>
    </div>

    <!-- ══ FOOTER HALAMAN ══ -->
    <div class="page-footer">
        <div class="footer-top">
            <div class="footer-company">{{ $mcu_meta['footer_company_name'] ?? 'PT. Andalan Artha Primanusa' }}</div>
            <div class="footer-detail">
                {{ $mcu_meta['footer_address'] ?? 'Jl. Plaju No.11 Kebon Melati, Tanah Abang Jakarta Pusat 10230 DKI Jakarta – Indonesia' }}
                <br>
                Email: {{ $mcu_meta['footer_email'] ?? 'corporatesecretary@andalan-nusantara.com' }}
                | {{ $mcu_meta['footer_website'] ?? 'www.andalan-nusantara.com' }}
            </div>
        </div>
        <div class="footer-bar"></div>
    </div>

</body>
</html>