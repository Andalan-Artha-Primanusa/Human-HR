<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Undangan Medical Check Up</title>
</head>
<body style="margin:0;padding:0;background:#f5f1ec;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#334155;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f1ec;padding:28px 12px;">
    <tr><td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">
        <tr><td align="center" style="padding:0 0 18px;"><a href="{{ config('app.url') }}" style="color:#3b2414;text-decoration:none;font-size:20px;font-weight:800;letter-spacing:.18em;">ANDALAN HR</a></td></tr>
        <tr><td style="background:#fff;border:1px solid #eadfd4;border-radius:18px;overflow:hidden;box-shadow:0 16px 45px rgba(59,36,20,.08);">
          <div style="height:6px;background:#a77d52;"></div>
          <div style="padding:34px 38px;">
            <div style="display:inline-block;background:#fffaf5;color:#8b5e3c;border:1px solid #eadfd4;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:800;">Medical Check Up</div>
            <h1 style="margin:18px 0 10px;color:#0f172a;font-size:24px;line-height:1.25;">Halo, {{ $candidateName }}</h1>
            <p style="margin:0 0 18px;color:#475569;font-size:15px;line-height:1.65;">Berikut undangan MCU untuk posisi <strong style="color:#0f172a;">{{ $jobTitle }}</strong>.</p>
            <div style="background:#fffaf5;border:1px solid #eadfd4;border-radius:14px;padding:18px 20px;color:#334155;font-size:15px;line-height:1.7;white-space:pre-wrap;">{{ $bodyContent }}</div>
            <p style="margin:24px 0 0;color:#475569;font-size:15px;line-height:1.65;">Salam hangat,<br><strong style="color:#0f172a;">Tim HR {{ $companyName }}</strong></p>
          </div>
        </td></tr>
        <tr><td align="center" style="padding:18px 10px 0;"><p style="margin:0;color:#8b735f;font-size:12px;line-height:1.55;">PT Andalan Artha Primanusa · recruitment@andalanarthaprimanusa.com</p></td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
