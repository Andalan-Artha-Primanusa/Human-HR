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
}
