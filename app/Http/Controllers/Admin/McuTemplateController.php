<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\McuTemplate;
use Illuminate\Http\Request;

class McuTemplateController extends Controller
{
    public function index()
    {
        $templates = McuTemplate::latest()->paginate(10);
        return view('admin.mcu-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.mcu-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_address' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'for_text' => 'nullable|string|max:255',
            'bu_name' => 'nullable|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'matrix_owner' => 'nullable|string|max:255',
            'package' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'result_emails' => 'nullable|string',
            'signer_name' => 'nullable|string|max:255',
            'signer_title' => 'nullable|string|max:255',
            'footer_company_name' => 'nullable|string|max:255',
            'footer_address' => 'nullable|string',
            'footer_email' => 'nullable|string|max:255',
            'footer_website' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('is_active') && $request->is_active) {
            McuTemplate::where('is_active', true)->update(['is_active' => false]);
        }

        McuTemplate::create($validated);

        return redirect()->route('admin.mcu-templates.index')->with('ok', 'Template MCU berhasil dibuat.');
    }

    public function edit(McuTemplate $mcuTemplate)
    {
        return view('admin.mcu-templates.edit', compact('mcuTemplate'));
    }

    public function update(Request $request, McuTemplate $mcuTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_address' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'for_text' => 'nullable|string|max:255',
            'bu_name' => 'nullable|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'matrix_owner' => 'nullable|string|max:255',
            'package' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'result_emails' => 'nullable|string',
            'signer_name' => 'nullable|string|max:255',
            'signer_title' => 'nullable|string|max:255',
            'footer_company_name' => 'nullable|string|max:255',
            'footer_address' => 'nullable|string',
            'footer_email' => 'nullable|string|max:255',
            'footer_website' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('is_active') && $request->is_active) {
            McuTemplate::where('id', '!=', $mcuTemplate->id)->where('is_active', true)->update(['is_active' => false]);
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = false;
        }

        $mcuTemplate->update($validated);

        return redirect()->route('admin.mcu-templates.index')->with('ok', 'Template MCU berhasil diperbarui.');
    }

    public function destroy(McuTemplate $mcuTemplate)
    {
        $mcuTemplate->delete();
        return redirect()->route('admin.mcu-templates.index')->with('ok', 'Template MCU berhasil dihapus.');
    }

    public function preview(McuTemplate $mcuTemplate)
    {
        // Prepare mock mcu_meta data untuk preview
        $mcu_meta = [
            'company_name' => $mcuTemplate->company_name ?? 'ANDALAN',
            'city' => $mcuTemplate->city ?? 'Jakarta',
            'doc_date' => now(),
            'doc_no' => 'No. Surat Preview',
            'project_name' => $mcuTemplate->project_name ?? 'PROJECT',
            'clinic_name' => $mcuTemplate->vendor_name ?? 'Nama Klinik / RS',
            'clinic_address' => $mcuTemplate->vendor_address ?? 'Alamat Vendor MCU',
            'subject' => $mcuTemplate->subject ?? 'Medical Check Up – Pre Employee',
            'for_text' => $mcuTemplate->for_text ?? 'Pre-Employment',
            'bu_name' => $mcuTemplate->bu_name ?? 'PT. Andalan Artha Primanusa',
            'matrix_owner' => $mcuTemplate->matrix_owner ?? 'Andalan Artha Primanusa',
            'package' => $mcuTemplate->package ?? 'Paket Standard',
            'mcu_date' => now()->addDays(7),
            'notes' => $mcuTemplate->notes ?? '1. Bagi kandidat berusia > 40 tahun, diwajibkan menjalani pemeriksaan treadmill.\n2. Mohon cocokan KTP asli dengan identitas kandidat yang akan diperiksa.',
            'result_emails' => $mcuTemplate->result_emails ?? 'email@example.com',
            'signer_name' => $mcuTemplate->signer_name ?? 'Roy/Hansen C. Saragi',
            'signer_title' => $mcuTemplate->signer_title ?? 'General Manager',
            'footer_company_name' => $mcuTemplate->footer_company_name ?? 'PT. Andalan Artha Primanusa',
            'footer_address' => $mcuTemplate->footer_address ?? 'Jl. Plaju No.11 Kebon Melati, Tanah Abang Jakarta Pusat 10230 DKI Jakarta – Indonesia',
            'footer_email' => $mcuTemplate->footer_email ?? 'corporatesecretary@andalan-nusantara.com',
            'footer_website' => $mcuTemplate->footer_website ?? 'www.andalan-nusantara.com',
        ];

        // Mock application dengan struktur lengkap untuk preview
        $application = (object) [
            'id' => 'PREVIEW-001',
            'user' => (object) [
                'id_employe' => 'EMP000001',
                'name' => 'Nama Kandidat (Preview)',
                'candidateProfile' => (object) [
                    'dob' => now()->subYears(30),
                ]
            ],
            'job' => (object) [
                'title' => 'Posisi (Preview)',
                'site' => (object) [
                    'code' => 'PJK001'
                ]
            ],
        ];

        // Generate HTML dari view
        $html = view('mcu.pdf', compact('application', 'mcu_meta'))->render();
        
        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setWarnings(false);

        return $pdf->stream('Preview-Template-MCU-' . $mcuTemplate->id . '.pdf');
    }
}
