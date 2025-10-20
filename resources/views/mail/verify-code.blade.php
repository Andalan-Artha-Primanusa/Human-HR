{{-- resources/views/mail/verify-code-inline.blade.php --}}
@php
  $appName  = $appName  ?? config('app.name', 'HUMAN Careers');
  $support  = $support  ?? config('mail.reply_to.address', config('mail.from.address'));
  $userName = $userName ?? null;
  // Pastikan $code (string 6 digit) & $ttlMinutes (int) dikirim dari Notification/Mailable
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light dark">
  <meta name="supported-color-schemes" content="light dark">
  <title>Kode Verifikasi • {{ $appName }}</title>

  <style>
    /* ===== Minimal Reset (aman untuk email clients) ===== */
    html,body { margin:0; padding:0; height:100%; }
    img { border:0; outline:0; display:block; -ms-interpolation-mode:bicubic; }
    table { border-collapse:collapse; }
    a[x-apple-data-detectors]{ color:inherit !important; text-decoration:underline !important; }
    /* ===== Dark Mode (Apple Mail/iOS) ===== */
    @media (prefers-color-scheme: dark) {
      body { background:#0b1220 !important; color:#f1f5f9 !important; }
      .card    { background:#1f2937 !important; border-color:#334155 !important; }
      .chip    { background:rgba(51,65,85,.5) !important; border-color:#475569 !important; color:#f1f5f9 !important; }
      .divider { background:#334155 !important; }
      .muted   { color:#cbd5e1 !important; }
      .accent  { color:#38bdf8 !important; }
      .codebox { background:rgba(51,65,85,.4) !important; border-color:#475569 !important; color:#f8fafc !important; }
    }
  </style>

  <!-- Preheader (tersembunyi, tampil di preview klien email) -->
  <style>.preheader{display:none!important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;mso-hide:all}</style>
</head>
<body style="margin:0;padding:0;background:#f8fafc;color:#0f172a;font-family:ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,Ubuntu,'Helvetica Neue',Arial,'Noto Sans','Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';line-height:1.5;">
  <div class="preheader">
    Kode verifikasi Anda: {{ chunk_split($code, 3, ' ') }} — berlaku {{ $ttlMinutes }} menit.
  </div>

  <!-- Outer wrapper -->
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
      <td align="center" style="padding:24px;">
        <!-- Card -->
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:600px;">
          <tr>
            <td class="card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 1px 2px rgba(0,0,0,.05);overflow:hidden;">
              <!-- Header -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-bottom:1px solid #e2e8f0;">
                <tr>
                  <td style="padding:20px;">
                    <table role="presentation" width="100%">
                      <tr>
                        <td valign="middle" align="left">
                          <img src="{{ asset('assets/foto2.png') }}" alt="{{ $appName }}" height="28" style="height:28px;">
                        </td>
                        <td valign="middle" align="right">
                          <span class="chip" style="display:inline-block;font-size:12px;font-weight:500;color:#1f2937;background:#eff6ff;border:1px solid #dbeafe;border-radius:9999px;padding:4px 12px;">
                            Verifikasi Akun
                          </span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Body -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="padding:24px;">
                    <p style="margin:0 0 6px 0;font-size:16px;font-weight:600;">
                      Halo {{ $userName ?: 'teman' }},
                    </p>
                    <p class="muted" style="margin:0;font-size:14px;color:#475569;">
                      Berikut kode verifikasi untuk akun Anda. Masukkan kode ini pada halaman verifikasi untuk menyelesaikan proses.
                    </p>

                    <!-- Code box -->
                    <div class="codebox"
                         style="margin:20px auto 16px auto;text-align:center;font-size:24px;font-weight:800;letter-spacing:.35em;color:#0f172a;background:#f8fafc;border:1px dashed #e2e8f0;border-radius:12px;padding:16px;">
                      {{ implode(' ', str_split($code)) }}
                    </div>

                    <p class="muted" style="margin:0;font-size:14px;color:#475569;">
                      Kode berlaku selama <strong>{{ $ttlMinutes }} menit</strong>. Demi keamanan, jangan bagikan kode ini kepada siapapun.
                    </p>

                    <!-- Divider -->
                    <div class="divider" style="height:1px;background:#e2e8f0;margin:16px 0;"></div>

                    <p class="muted" style="margin:0;font-size:12px;color:#64748b;line-height:1.4;">
                      Tidak merasa meminta verifikasi? Abaikan email ini. Butuh bantuan? Balas email ini atau hubungi
                      <a class="accent" href="mailto:{{ $support }}" style="color:#0284c7;text-decoration:underline;">{{ $support }}</a>.
                    </p>

                    <p class="muted" style="margin:16px 0 0 0;text-align:center;font-size:11px;color:#64748b;">
                      © {{ date('Y') }} {{ $appName }} — Semua hak dilindungi.
                    </p>
                  </td>
                </tr>
              </table>
              <!-- /Body -->
            </td>
          </tr>
        </table>
        <!-- /Card -->
      </td>
    </tr>
  </table>
</body>
</html>
