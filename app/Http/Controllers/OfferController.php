<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfferRequest;
use App\Models\JobApplication;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\OfferLetterMail;
use App\Models\Poh;
use App\Models\Site;

class OfferController extends Controller
{
    protected function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole(['hr', 'superadmin']), 403, 'Forbidden.');
    }

    /**
     * Admin: buat draft offer
     */
    public function store(StoreOfferRequest $request, JobApplication $application)
    {
        $data = $request->validated();

        $offer = $application->offer()->create([
            'status' => 'draft',
            'salary' => [
                'gross' => (float) $data['gross_salary'],
                'allowance' => isset($data['allowance']) ? (float) $data['allowance'] : 0,
            ],
            'body_template' => $data['html'] ?? $data['notes'] ?? null,
            'meta' => $data['meta'] ?? [],
        ]);

        // opsional: ubah stage ke 'offer'
        $application->update(['current_stage' => 'offer']);

        if ($application->user && $application->user->email) {
            try {
                $mail = new OfferLetterMail($offer);
                $mail->bodyContent = $data['html'] ?? $data['notes'] ?? "Terlampir adalah dokumen Offering Letter Anda.";
                Mail::to($application->user->email)->send($mail);
            } catch (\Exception $e) {
                \Log::error('Failed to send OfferLetterMail: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.applications.index')->with('ok', 'Draft offer dibuat.');
    }

    /**
     * Update existing offer
     */
    public function update(Request $request, Offer $offer)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'gross' => 'required|numeric|min:0',
            'allowance' => 'required|numeric|min:0',
            'body' => 'required|string',
            'status' => ['nullable', Rule::in(['draft', 'sent', 'accepted', 'rejected'])],
            
            // Meta fields
            'doc_no'          => 'nullable|string',
            'grade_level'     => 'nullable|string',
            'poh'             => 'nullable|string',
            'lokasi'          => 'nullable|string',
            'contract_status' => 'nullable|string',
            'join_date'       => 'nullable|date',
            'working_hours'   => 'nullable|string',
            'working_schedule'=> 'nullable|string',
            'meals_allowance' => 'nullable|string',
            'overtime'        => 'nullable|string',
            'tax_borne_by'    => 'nullable|string',
            'deductions'      => 'nullable|string',
            'signer_name'     => 'nullable|string',
            'signer_title'    => 'nullable|string',
            'company'         => 'nullable|string',
            'footer_code'     => 'nullable|string',
            'footer_version'  => 'nullable|string',
            'footer_page_text'=> 'nullable|string',
        ]);

        $meta = $offer->meta ?? [];
        $metaFields = [
            'doc_no', 'grade_level', 'poh', 'lokasi', 'contract_status', 
            'join_date', 'working_hours', 'working_schedule', 
            'meals_allowance', 'overtime', 'tax_borne_by', 'deductions',
            'signer_name', 'signer_title', 'company', 'footer_code', 'footer_version',
            'footer_page_text'
        ];

        foreach ($metaFields as $f) {
            if ($request->has($f)) {
                $meta[$f] = $data[$f];
            }
        }

        $offer->update([
            'salary' => [
                'gross' => (float) $data['gross'],
                'allowance' => (float) $data['allowance'],
            ],
            'body_template' => $data['body'],
            'status' => $data['status'] ?? $offer->status,
            'meta' => $meta,
        ]);

        return back()->with('ok', 'Offer berhasil diperbarui.');
    }

    /**
     * Render Offering Letter ke PDF (A4)
     * - Default: stream (preview di browser)
     * - ?dl=1   : force download
     */
    public function pdf(Request $request, Offer $offer)
    {
        $this->ensureAdmin($request);

        $offer->load('application.user', 'application.job', 'application.job.site');

        // === Render HTML dari Blade (sesuai template foto) ===
        if (view()->exists('offers.pdf')) {
            $html = view('offers.pdf', compact('offer'))->render();
        } else {
            // Fallback sederhana jika view belum ada
            $app = $offer->application;
            $user = $app?->user?->name ?? '—';
            $title = $app?->job?->title ?? '—';
            $site = $app?->job?->site?->code ?? '—';
            $gross = number_format((float) ($offer->salary['gross'] ?? 0), 0, ',', '.');
            $allow = number_format((float) ($offer->salary['allowance'] ?? 0), 0, ',', '.');
            $date = now()->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y');

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
                '%%USER%%' => e($user),
                '%%TITLE%%' => e($title),
                '%%SITE%%' => e($site),
                '%%DATE%%' => e($date),
                '%%GROSS%%' => e($gross),
                '%%ALLOW%%' => e($allow),
            ]);
        }

        // === Generate PDF ===
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')         // A4 portrait
            ->setWarnings(false);

        $filename = 'OfferingLetter-' . $offer->id . '.pdf';

        // ?dl=1 untuk download, selain itu stream preview
        return $request->boolean('dl')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    /**
     * Listing offers (filter q & status)
     */
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $payload = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['draft', 'sent', 'accepted', 'rejected'])],
        ]);

        $qRaw = (string) ($payload['q'] ?? '');
        $q = Str::limit(
            preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '',
            120,
            ''
        );
        $like = $q !== '' ? '%' . addcslashes($q, '\\%_') . '%' : null;
        $status = (string) ($payload['status'] ?? '');

        $offers = Offer::query()
            ->select(['id', 'application_id', 'status', 'salary', 'created_at'])
            ->with([
                'application.user:id,name',
                'application.job:id,title,site_id,company_id,level',
                'application.job.site:id,code,name',
                'application.job.company:id,name',
                'application.poh:id,name',
            ])
            ->when($like !== null, function ($qq) use ($like) {
                $qq->where(function ($w) use ($like) {
                    $w->whereHas('application.user', fn($u) => $u->where('name', 'like', $like))
                        ->orWhereHas('application.job', fn($j) => $j->where('title', 'like', $like));
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pohs = Poh::all(['id', 'name']);
        $sites = Site::all(['id', 'code', 'name']);

        return view('admin.offers.index', compact('offers', 'q', 'status', 'pohs', 'sites'));
    }
}
