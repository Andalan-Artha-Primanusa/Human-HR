<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Offer;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OfferController extends Controller
{
  /**
   * Admin: buat draft offer
   */
  public function store(Request $request, JobApplication $application)
  {
    $data = $request->validate([
      'gross_salary' => 'required|numeric|min:0',
      'allowance'    => 'nullable|numeric|min:0',
      'notes'        => 'nullable|string',
      'html'         => 'nullable|string', // jika kirim HTML custom
      'meta'         => 'sometimes|array', // meta opsional (doc_no, level, poh, join_date, dll)
    ]);

    $offer = $application->offer()->create([
      'status'        => 'draft',
      'salary'        => [
        'gross'     => (float) $data['gross_salary'],
        'allowance' => isset($data['allowance']) ? (float) $data['allowance'] : 0,
      ],
      'body_template' => $data['html'] ?? null,
      'meta'          => $data['meta'] ?? [],
    ]);

    // opsional: ubah stage ke 'offer'
    $application->update(['current_stage' => 'offer']);

    return redirect()->route('admin.applications.index')->with('ok', 'Draft offer dibuat.');
  }

  /**
   * Render Offering Letter ke PDF (A4)
   * - Default: stream (preview di browser)
   * - ?dl=1   : force download
   */
  public function pdf(Offer $offer)
  {
    $offer->load('application.user', 'application.job', 'application.job.site');

    // === Render HTML dari Blade (sesuai template foto) ===
    if (view()->exists('offers.pdf')) {
      $html = view('offers.pdf', compact('offer'))->render();
    } else {
      // Fallback sederhana jika view belum ada
      $app    = $offer->application;
      $user   = $app?->user?->name ?? '—';
      $title  = $app?->job?->title ?? '—';
      $site   = $app?->job?->site?->code ?? '—';
      $gross  = number_format((float)($offer->salary['gross'] ?? 0), 0, ',', '.');
      $allow  = number_format((float)($offer->salary['allowance'] ?? 0), 0, ',', '.');
      $date   = now()->timezone(config('app.timezone','Asia/Jakarta'))->format('d M Y');

      $html = <<<'HTML'
<!doctype html><html><head><meta charset="utf-8"><title>Offering Letter</title>
<style>@page{margin:28px}body{font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:12px;color:#111}
h1{font-size:18px;margin:0 0 8px}.tbl{width:100%;border-collapse:collapse;margin-top:8px}
.tbl th,.tbl td{border:1px solid #ddd;padding:6px 8px}</style></head><body>
<h1>Offering Letter</h1>
<p><strong>Candidate:</strong> %%USER%%<br>
<strong>Position:</strong> %%TITLE%% @ %%SITE%%<br>
<strong>Date:</strong> %%DATE%%</p>
<table class="tbl">
<tr><th>Gross Salary</th><td>Rp %%GROSS%%</td></tr>
<tr><th>Allowance</th><td>Rp %%ALLOW%%</td></tr>
</table>
</body></html>
HTML;
      $html = strtr($html, [
        '%%USER%%'  => e($user),
        '%%TITLE%%' => e($title),
        '%%SITE%%'  => e($site),
        '%%DATE%%'  => e($date),
        '%%GROSS%%' => e($gross),
        '%%ALLOW%%' => e($allow),
      ]);
    }

    // === Generate PDF ===
    $pdf = Pdf::loadHTML($html)
      ->setPaper('a4')         // A4 portrait
      ->setWarnings(false);

    $filename = 'OfferingLetter-'.$offer->id.'.pdf';

    // ?dl=1 untuk download, selain itu stream preview
    return request()->boolean('dl')
      ? $pdf->download($filename)
      : $pdf->stream($filename);
  }

  /**
   * Listing offers (filter q & status)
   */
  public function index(Request $request)
  {
    $q      = (string) $request->query('q', '');
    $status = (string) $request->query('status', '');

    $offers = Offer::query()
      ->with([
        'application.user:id,name',
        'application.job:id,title,site_id',
        'application.job.site:id,code,name',
      ])
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
