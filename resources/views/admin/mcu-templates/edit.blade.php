@extends('layouts.app')

@section('title', 'Admin · Edit MCU Template • karir-andalan')

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1000px] px-4 py-6 space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.mcu-templates.index') }}" class="p-2 bg-white border border-slate-200 rounded-xl text-slate-500 hover:text-slate-900 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Edit Template MCU</h1>
    </div>

    <form action="{{ route('admin.mcu-templates.update', $mcuTemplate) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Informasi Dasar & Header</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Template (Internal)</label>
                    <input type="text" name="name" value="{{ old('name', $mcuTemplate->name) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Contoh: Template Standar Jakarta" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Perusahaan (Logo Text)</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $mcuTemplate->company_name) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="ANDALAN">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kota Penerbitan</label>
                    <input type="text" name="city" value="{{ old('city', $mcuTemplate->city) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Jakarta">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Project (Red Box)</label>
                    <input type="text" name="project_name" value="{{ old('project_name', $mcuTemplate->project_name) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="PROJECT">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Subject / Hal</label>
                    <input type="text" name="subject" value="{{ old('subject', $mcuTemplate->subject) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Medical Check Up – Pre Employee">
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Informasi Vendor & Body</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Vendor (Default)</label>
                    <input type="text" name="vendor_name" value="{{ old('vendor_name', $mcuTemplate->vendor_name) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Nama Klinik / RS">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Vendor (Default)</label>
                    <textarea name="vendor_address" rows="2" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Alamat lengkap vendor mcu">{{ old('vendor_address', $mcuTemplate->vendor_address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Text "for" (e.g. Pre-Employment)</label>
                    <input type="text" name="for_text" value="{{ old('for_text', $mcuTemplate->for_text) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="<<for>>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama BU (PT. <<BU>>)</label>
                    <input type="text" name="bu_name" value="{{ old('bu_name', $mcuTemplate->bu_name) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="PT. <<BU>>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Matrix MCU PT <<Owner>></label>
                    <input type="text" name="matrix_owner" value="{{ old('matrix_owner', $mcuTemplate->matrix_owner) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="<<Owner>>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Package</label>
                    <input type="text" name="package" value="{{ old('package', $mcuTemplate->package) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Contoh: Paket A">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Catatan (Poin 1, 2, dst)</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="1. Bagi kandidat berusia > 40 tahun...&#10;2. Mohon cocokan KTP asli...">{{ old('notes', $mcuTemplate->notes) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Hasil (List Email per Baris)</label>
                    <textarea name="result_emails" rows="3" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="email1@pt-aap.com&#10;email2@pt-aap.com">{{ old('result_emails', $mcuTemplate->result_emails) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Footer & Tanda Tangan</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Penanda Tangan</label>
                    <input type="text" name="signer_name" value="{{ old('signer_name', $mcuTemplate->signer_name) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Roy/Hansen C. Saragi">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jabatan Penanda Tangan</label>
                    <input type="text" name="signer_title" value="{{ old('signer_title', $mcuTemplate->signer_title) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="General Manager">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Perusahaan Footer</label>
                    <input type="text" name="footer_company_name" value="{{ old('footer_company_name', $mcuTemplate->footer_company_name) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="PT. Andalan Artha Primanusa">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Footer</label>
                    <textarea name="footer_address" rows="2" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Jl. Plaju No.11 Kebon Melati...">{{ old('footer_address', $mcuTemplate->footer_address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Footer</label>
                    <input type="text" name="footer_email" value="{{ old('footer_email', $mcuTemplate->footer_email) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="corporatesecretary@andalan-nusantara.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Website Footer</label>
                    <input type="text" name="footer_website" value="{{ old('footer_website', $mcuTemplate->footer_website) }}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="www.andalan-nusantara.com">
                </div>
                <div class="md:col-span-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $mcuTemplate->is_active ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#a77d52]/20 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#a77d52]"></div>
                        <span class="ms-3 text-sm font-medium text-slate-700">Set sebagai template aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.mcu-templates.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 font-semibold hover:bg-slate-50 transition shadow-sm">Batal</a>
            <button type="submit" class="px-8 py-2.5 bg-[#a77d52] text-white rounded-xl font-semibold hover:bg-[#8b5e3c] transition shadow-md shadow-[#a77d52]/20">Perbarui Template</button>
        </div>
    </form>
</div>
@endsection
