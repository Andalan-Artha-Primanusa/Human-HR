<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Offer;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OfferController extends Controller
{
  // Admin: buat draft offer
  public function store(Request $request, JobApplication $application)
  {
    $data = $request->validate([
      'gross_salary' => 'required|numeric|min:0',
      'allowance'    => 'nullable|numeric|min:0',
      'notes'        => 'nullable|string',
      'html'         => 'nullable|string', // jika kirim HTML custom
    ]);

    $offer = $application->offer()->create([
      'status'        => 'draft',
      'salary'        => [
        'gross'     => (float)$data['gross_salary'],
        'allowance' => isset($data['allowance']) ? (float)$data['allowance'] : 0,
      ],
      'body_template' => $data['html'] ?? null,
    ]);

    // opsional: ubah stage ke 'offer'
    $application->update(['current_stage' => 'offer']);

    return redirect()->route('admin.applications.index')->with('ok', 'Draft offer dibuat.');
  }

  public function pdf(Offer $offer)
  {
    $offer->load('application.user', 'application.job', 'application.job.site');

    if (view()->exists('offers.pdf')) {
      $html = view('offers.pdf', compact('offer'))->render();
    } elseif (view()->exists('offers._fallback')) {
      $html = view('offers._fallback', compact('offer'))->render();
    } else {
      $app   = $offer->application;
      $user  = $app?->user?->name ?? '—';
      $title = $app?->job?->title ?? '—';
      $site  = $app?->job?->site?->code ?? '—';
      $gross = number_format((float)($offer->salary['gross'] ?? 0), 0, ',', '.');
      $allow = number_format((float)($offer->salary['allowance'] ?? 0), 0, ',', '.');
      $status = strtoupper($offer->status ?? 'draft');
      $date = now()->format('d M Y');

      // ▼▼▼ TIDAK BOLEH ADA SPASI/TAB SEBELUM <<<'HTML' MAUPUN PENUTUP HTML; ▼▼▼
      $html = <<<'HTML'
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Offering Letter</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#111; font-size:12px; line-height:1.6; }
    .wrap { padding: 28px; }
    h1 { font-size: 20px; margin: 0 0 8px; }
    h2 { font-size: 14px; margin: 16px 0 6px; }
    .table { width:100%; border-collapse: collapse; margin-top: 10px; }
    .table th, .table td { border:1px solid #ddd; padding:8px 10px; }
    .table th { background:#f5f6f8; text-align:left; }
    .muted { color:#666; }
    .sign { margin-top: 40px; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Offering Letter</h1>
    <div class="meta">
      <div><strong>Candidate:</strong> %%USER%%</div>
      <div><strong>Position:</strong> %%TITLE%% @ %%SITE%%</div>
      <div><strong>Offer ID:</strong> #%%ID%%</div>
      <div><strong>Status:</strong> %%STATUS%%</div>
      <div><strong>Date:</strong> %%DATE%%</div>
    </div>

    <h2>Compensation</h2>
    <table class="table">
      <tr>
        <th>Gross Salary</th>
        <td>Rp %%GROSS%%</td>
      </tr>
      <tr>
        <th>Allowance</th>
        <td>Rp %%ALLOW%%</td>
      </tr>
    </table>

    %%NOTES%%
    <div class="sign">
      <p class="muted">Regards,</p>
      <p><strong>Human Resources</strong></p>
    </div>
  </div>
</body>
</html>
HTML;
      // ▲▲▲ PENUTUP "HTML;" JUGA HARUS DI KOLOM 0 (NO INDENT) ▲▲▲

      $notesBlock = !empty($offer->body_template)
        ? '<h2>Notes</h2>' . $offer->body_template
        : '';

      $repl = [
        '%%USER%%'   => e($user),
        '%%TITLE%%'  => e($title),
        '%%SITE%%'   => e($site),
        '%%ID%%'     => (string) $offer->id,
        '%%STATUS%%' => e($status),
        '%%DATE%%'   => e($date),
        '%%GROSS%%'  => e($gross),
        '%%ALLOW%%'  => e($allow),
        '%%NOTES%%'  => $notesBlock,
      ];
      $html = strtr($html, $repl);
    }

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
    return $pdf->download('OfferingLetter-' . $offer->id . '.pdf');
  }


  public function index(Request $request)
  {
    $q      = (string) $request->query('q', '');
    $status = (string) $request->query('status', '');

    $offers = Offer::query()
      ->with(['application.user:id,name', 'application.job:id,title,site_id', 'application.job.site:id,code'])
      ->when($q, function ($qq) use ($q) {
        $qq->whereHas('application.user', fn($u) => $u->where('name', 'like', "%{$q}%"))
          ->orWhereHas('application.job', fn($j) => $j->where('title', 'like', "%{$q}%"));
      })
      ->when($status, fn($qq) => $qq->where('status', $status))
      ->latest()
      ->paginate(15);

    return view('admin.offers.index', compact('offers', 'q', 'status'));
  }
}
