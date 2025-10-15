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

    // Admin: cetak PDF offer
    public function pdf(Offer $offer)
    {
        $offer->load('application.user','application.job');

        // Jika tidak ada view 'offers.pdf', fallback ke HTML sederhana
        $html = view()->exists('offers.pdf')
            ? view('offers.pdf', compact('offer'))->render()
            : view('offers._fallback', compact('offer'))->render();

        $pdf = Pdf::loadHTML($html);
        return $pdf->download('OfferingLetter-'.$offer->id.'.pdf');
    }
}
