<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verifikasi Email</title>
</head>
<body style="margin:0;padding:0;background:#f5f1ec;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#334155;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f1ec;padding:28px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">
          <tr>
            <td align="center" style="padding:0 0 18px;">
              <a href="{{ config('app.url') }}" style="color:#3b2414;text-decoration:none;font-size:20px;font-weight:800;letter-spacing:.18em;">ANDALAN HR</a>
            </td>
          </tr>
          <tr>
            <td style="background:#ffffff;border:1px solid #eadfd4;border-radius:18px;overflow:hidden;box-shadow:0 16px 45px rgba(59,36,20,.08);">
              <div style="height:6px;background:#a77d52;"></div>
              <div style="padding:34px 38px;">
                <div style="display:inline-block;background:#fffaf5;color:#8b5e3c;border:1px solid #eadfd4;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:800;">Verifikasi Akun</div>
                <h1 style="margin:18px 0 10px;color:#0f172a;font-size:24px;line-height:1.25;">Halo, {{ $user->name }}</h1>
                <p style="margin:0 0 18px;color:#475569;font-size:15px;line-height:1.65;">Terima kasih sudah mendaftar di Andalan HR. Verifikasi email kamu agar akun bisa dipakai untuk melamar dan memantau proses rekrutmen.</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0;">
                  <tr>
                    <td align="center">
                      <a href="{{ $url }}" target="_blank" rel="noopener" style="display:inline-block;background:#8b5e3c;color:#ffffff;text-decoration:none;border-radius:12px;padding:14px 26px;font-size:14px;font-weight:800;">Verifikasi Email</a>
                    </td>
                  </tr>
                </table>
                <p style="margin:0 0 12px;color:#475569;font-size:15px;line-height:1.65;">Kalau kamu tidak membuat akun, abaikan email ini.</p>
                <div style="border-top:1px solid #eadfd4;margin-top:26px;padding-top:18px;">
                  <p style="margin:0 0 8px;color:#64748b;font-size:12px;line-height:1.55;">Jika tombol tidak bisa dibuka, salin link berikut ke browser:</p>
                  <a href="{{ $url }}" style="color:#8b5e3c;font-size:12px;word-break:break-all;">{{ $url }}</a>
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:18px 10px 0;">
              <p style="margin:0;color:#8b735f;font-size:12px;line-height:1.55;">Email ini dikirim otomatis oleh Andalan HR.<br>PT Andalan Artha Primanusa · recruitment@andalanarthaprimanusa.com</p>
              <p style="margin:8px 0 0;color:#8b735f;font-size:12px;">&copy; {{ date('Y') }} PT Andalan Artha Primanusa. All rights reserved.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
