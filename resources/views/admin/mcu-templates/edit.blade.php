@extends('layouts.app')

@section('title', 'Admin · Edit MCU Template • karir-andalan')

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $GREEN_FOOTER = '#8b9f6f';
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1200px] px-4 py-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.mcu-templates.index') }}" class="p-2 transition bg-white border shadow-sm border-slate-200 rounded-xl text-slate-500 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Edit Template MCU</h1>
            <p class="mt-1 text-sm text-slate-500">Sesuaikan format dan isi surat undangan MCU</p>
        </div>
    </div>

    <form action="{{ route('admin.mcu-templates.update', $mcuTemplate) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1: INFORMASI TEMPLATE -->
        <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl">
            <div class="px-6 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-slate-100">
                <h2 class="text-sm font-bold tracking-wider uppercase text-slate-600">📋 Informasi Template</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="md:col-span-3">
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Nama Template (Untuk Admin)</label>
                        <input type="text" name="name" value="{{ old('name', $mcuTemplate->name) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition font-medium" placeholder="Contoh: Template Jakarta Pre-Employment" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: HEADER (Logo & Info Penerbitan) -->
        <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl">
            <div class="px-6 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-slate-100">
                <h2 class="text-sm font-bold tracking-wider uppercase text-slate-600">📄 Header Surat</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Nama Perusahaan (Logo)</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $mcuTemplate->company_name) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="ANDALAN">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Kota Penerbitan</label>
                        <input type="text" name="city" value="{{ old('city', $mcuTemplate->city) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Jakarta">
                    </div>
                </div>
                <div class="p-4 border border-red-200 rounded-lg bg-red-50">
                    <label class="block mb-2 text-sm font-semibold text-red-800">🚨 Nama Project (Kotak Merah)</label>
                    <input type="text" name="project_name" value="{{ old('project_name', $mcuTemplate->project_name) }}" class="w-full px-4 py-2.5 bg-white border border-red-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition font-bold" placeholder="PROJECT">
                </div>
            </div>
        </div>

        <!-- SECTION 3: PENERIMA & SUBJECT -->
        <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl">
            <div class="px-6 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-slate-100">
                <h2 class="text-sm font-bold tracking-wider uppercase text-slate-600">✉️ Penerima & Subject</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Nama Vendor Penerima</label>
                        <input type="text" name="vendor_name" value="{{ old('vendor_name', $mcuTemplate->vendor_name) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Nama Klinik / RS">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Subject / Hal</label>
                        <input type="text" name="subject" value="{{ old('subject', $mcuTemplate->subject) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Medical Check Up – Pre Employee">
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Alamat Vendor Penerima</label>
                    <textarea name="vendor_address" rows="2" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Alamat lengkap vendor mcu">{{ old('vendor_address', $mcuTemplate->vendor_address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 4: ISI SURAT -->
        <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl">
            <div class="px-6 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-slate-100">
                <h2 class="text-sm font-bold tracking-wider uppercase text-slate-600">📝 Isi Surat</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Text "For" (e.g. Pre-Employment)</label>
                        <input type="text" name="for_text" value="{{ old('for_text', $mcuTemplate->for_text) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Pre-Employment">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Nama BU (PT. ...)</label>
                        <input type="text" name="bu_name" value="{{ old('bu_name', $mcuTemplate->bu_name) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="PT. Andalan Artha Primanusa">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Matrix MCU PT ...</label>
                        <input type="text" name="matrix_owner" value="{{ old('matrix_owner', $mcuTemplate->matrix_owner) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Andalan Artha Primanusa">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Package MCU</label>
                        <input type="text" name="package" value="{{ old('package', $mcuTemplate->package) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Paket Standard">
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Catatan (Bullet Points)</label>
                    <textarea name="notes" rows="4" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition font-mono text-sm" placeholder="1. Bagi kandidat berusia > 40 tahun, diwajibkan menjalani pemeriksaan treadmill.&#10;2. Mohon cocokan KTP asli dengan identitas kandidat yang akan diperiksa.">{{ old('notes', $mcuTemplate->notes) }}</textarea>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Email Penerima Hasil MCU (Per Baris)</label>
                    <textarea name="result_emails" rows="4" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition font-mono text-sm" placeholder="email1@pt-aap.com&#10;email2@pt-aap.com&#10;email3@pt-aap.com">{{ old('result_emails', $mcuTemplate->result_emails) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 5: TANDA TANGAN -->
        <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl">
            <div class="px-6 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-slate-100">
                <h2 class="text-sm font-bold tracking-wider uppercase text-slate-600">✍️ Penanda Tangan</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Nama Penanda Tangan</label>
                        <input type="text" name="signer_name" value="{{ old('signer_name', $mcuTemplate->signer_name) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Roy Hansen C. Saragih">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Jabatan Penanda Tangan</label>
                        <input type="text" name="signer_title" value="{{ old('signer_title', $mcuTemplate->signer_title) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="General Manager">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 6: FOOTER (GREEN SECTION) -->
        <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl">
            <div class="px-6 py-3 border-b border-slate-100" style="background: linear-gradient(to right, {{ $GREEN_FOOTER }}, #7a8d63)">
                <h2 class="text-sm font-bold tracking-wider text-white uppercase">🏢 Bagian Footer (Hijau)</h2>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Nama Perusahaan Footer</label>
                    <input type="text" name="footer_company_name" value="{{ old('footer_company_name', $mcuTemplate->footer_company_name) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="PT. Andalan Artha Primanusa">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Alamat Perusahaan Footer</label>
                    <textarea name="footer_address" rows="2" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Jl. Plaju No.11 Kebon Melati, Tanah Abang Jakarta Pusat 10230 DKI Jakarta – Indonesia">{{ old('footer_address', $mcuTemplate->footer_address) }}</textarea>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Email Footer</label>
                        <input type="email" name="footer_email" value="{{ old('footer_email', $mcuTemplate->footer_email) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="corporatesecretary@andalan-nusantara.com">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Website Footer</label>
                        <input type="text" name="footer_website" value="{{ old('footer_website', $mcuTemplate->footer_website) }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="www.andalan-nusantara.com">
                    </div>
                </div>
            </div>
        </div>

        <!-- STATUS -->
        <div class="p-6 bg-white border shadow-sm border-slate-200 rounded-2xl">
            <label class="inline-flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $mcuTemplate->is_active ? 'checked' : '' }} class="sr-only peer">
                <div class="relative w-12 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#a77d52]/20 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:start-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                <div>
                    <span class="text-sm font-semibold text-slate-700">Set sebagai template aktif</span>
                    <p class="text-xs text-slate-500 mt-0.5">Template aktif akan digunakan sebagai default ketika mengirim MCU</p>
                </div>
            </label>
        </div>

        <!-- ACTIONS -->
        <div class="flex justify-between gap-3 pt-4">
            <a href="{{ route('admin.mcu-templates.preview', $mcuTemplate) }}" target="_blank" class="px-6 py-2.5 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg font-semibold hover:bg-blue-100 transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Lihat Preview
            </a>
            <div class="flex gap-3">
                <a href="{{ route('admin.mcu-templates.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 rounded-lg text-slate-700 font-semibold hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-[#a77d52] text-white rounded-lg font-semibold hover:bg-[#8b5e3c] transition shadow-md shadow-[#a77d52]/20">Perbarui Template</button>
            </div>
        </div>
    </form>
</div>
@endsection
