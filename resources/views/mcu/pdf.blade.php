<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Undangan MCU</title>
    <style>
        @page { margin: 30px 50px 80px 50px; }
        body { font-family: 'Arial', sans-serif; font-size: 10pt; color: #000; line-height: 1.3; }
        
        .header-logo { margin-bottom: 20px; }
        .logo-img { height: 60px; }
        
        .top-info { position: relative; margin-bottom: 30px; }
        .top-left { float: left; width: 60%; }
        .top-right { float: right; width: 35%; text-align: right; }
        
        .project-box {
            display: inline-block;
            border: 2px solid red;
            padding: 5px 10px;
            color: #000;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 10px;
        }
        
        .recipient { margin-bottom: 20px; clear: both; }
        .subject { font-weight: bold; text-decoration: underline; margin-bottom: 15px; }
        
        .content { margin-bottom: 15px; text-align: justify; }
        
        .data-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 9pt; }
        .data-table th { background-color: #d1d5db; border: 1px solid #000; padding: 6px; text-align: center; font-weight: bold; }
        .data-table td { border: 1px solid #000; padding: 6px; text-align: center; }
        
        .notes { margin-top: 15px; }
        .notes-title { font-weight: bold; margin-bottom: 5px; }
        .notes-list { margin-left: 20px; }
        
        .email-info { margin-top: 15px; }
        .email-list { margin-left: 20px; color: blue; text-decoration: underline; }
        
        .footer-sign { margin-top: 30px; }
        .signature-img { height: 80px; margin: 10px 0; }
        .signer-name { font-weight: bold; text-decoration: underline; margin-bottom: 0; }
        .signer-title { margin-top: 0; }
        
        .page-footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 8pt;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .footer-company { font-weight: bold; margin-bottom: 2px; }
        .footer-detail { color: #555; }

        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="header-logo">
        {{-- Gunakan logo dari public path jika ada, atau teks jika tidak --}}
        <h1 style="color: #7a4f2a; margin: 0; font-size: 24pt; font-weight: bold;">
            @if($mcu_meta['company_name'] ?? false)
                {{ $mcu_meta['company_name'] }}
            @else
                ANDALAN
            @endif
        </h1>
    </div>

    <div class="top-info">
        <div class="top-left">
            {{ $mcu_meta['city'] ?? 'Jakarta' }}, {{ \Carbon\Carbon::parse($mcu_meta['doc_date'] ?? now())->format('d F Y') }}<br>
            No. : {{ $mcu_meta['doc_no'] ?? '<<Nomor Surat>>' }}
        </div>
        <div class="top-right">
            <div class="project-box">
                {{ $mcu_meta['project_name'] ?? '<<Project>>' }} PROJECT
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="recipient">
        Kepada Yth.<br>
        <strong>{{ $mcu_meta['clinic_name'] ?? '<<Vendor>>' }}</strong><br>
        {{ $mcu_meta['clinic_address'] ?? '<<Alamat Lengkap Vendor MCU>>' }}
    </div>

    <div class="subject">
        Hal: {{ $mcu_meta['subject'] ?? 'Medical Check Up – Pre Employee' }}
    </div>

    <p>Dengan Hormat,</p>

    <div class="content">
        Sehubungan dengan program pelaksanaan MCU ({{ $mcu_meta['for_text'] ?? '<<for>>' }}) PT. {{ $mcu_meta['bu_name'] ?? '<<BU>>' }} (AAP Group), bersama ini kami mengirimkan karyawan kami untuk dilakukan pemeriksaan kesehatan awal sesuai dengan jenis pemeriksaan yang telah ditentukan oleh Matrix MCU PT {{ $mcu_meta['matrix_owner'] ?? '<<Owner>>' }} kepada nama sebagai berikut:
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>NIK</th>
                <th>Name</th>
                <th>Position</th>
                <th>Date Of Born</th>
                <th>Age</th>
                <th>MCU Date</th>
                <th>Project Code</th>
                <th>Package</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $application->user->id_employe ?? '<<NIK>>' }}</td>
                <td>{{ $application->user->name }}</td>
                <td>{{ $application->job->title }}</td>
                <td>{{ $application->user->candidateProfile->dob ? \Carbon\Carbon::parse($application->user->candidateProfile->dob)->format('d/m/Y') : '<<Date Of Born>>' }}</td>
                <td>{{ $application->user->candidateProfile->dob ? \Carbon\Carbon::parse($application->user->candidateProfile->dob)->age : '<<Age>>' }}</td>
                <td>{{ \Carbon\Carbon::parse($mcu_meta['mcu_date'] ?? now())->format('d/m/Y') }}</td>
                <td>{{ $application->job->site->code ?? '<<Project Code>>' }}</td>
                <td>{{ $mcu_meta['package'] ?? '<<Package>>' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="notes">
        <div class="notes-title">Catatan:</div>
        <div class="notes-list">
            {!! nl2br(e($mcu_meta['notes'] ?? "1. Bagi kandidat berusia > 40 tahun, diwajibkan menjalani pemeriksaan treadmill.\n2. Mohon cocokan KTP asli dengan identitas kandidat yang akan diperiksa.")) !!}
        </div>
    </div>

    <div class="email-info">
        Hasil MCU harap dikirimkan ke email berikut:
        <ul class="email-list" style="list-style: disc; margin-top: 5px;">
            @php
                $emails = preg_split('/\r\n|\r|\n/', $mcu_meta['result_emails'] ?? "hendy.fardiansyah@pt-aap.com\nvidya.paramitha.putri@pt-aap.com\nrizal.abu@pt-aap.com");
            @endphp
            @foreach($emails as $email)
                @if(trim($email))
                    <li>{{ trim($email) }}</li>
                @endif
            @endforeach
        </ul>
    </div>

    <div class="footer-sign">
        Hormat Kami<br>
        <strong>{{ $mcu_meta['footer_company_name'] ?? 'Andalan Artha Primanusa' }}</strong><br>
        
        <div class="signature-space" style="height: 60px;">
            {{-- Ruang tanda tangan --}}
        </div>
        
        <div class="signer-name">{{ $mcu_meta['signer_name'] ?? 'Roy/Hansen C. Saragi' }}</div>
        <div class="signer-title">{{ $mcu_meta['signer_title'] ?? 'General Manager' }}</div>
    </div>

    <div class="page-footer">
        <div class="footer-company">PT. Andalan Artha Primanusa</div>
        <div class="footer-detail">
            {{ $mcu_meta['footer_address'] ?? 'Jl. Plaju No.11 Kebon Melati, Tanah Abang Jakarta Pusat 10230 DKI Jakarta – Indonesia' }}<br>
            Email: {{ $mcu_meta['footer_email'] ?? 'corporatesecretary@andalan-nusantara.com' }} | {{ $mcu_meta['footer_website'] ?? 'www.andalan-nusantara.com' }}
        </div>
    </div>
</body>
</html>
