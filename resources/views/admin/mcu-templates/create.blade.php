@extends('layouts.app')

@section('title', 'Admin · Buat MCU Template • karir-andalan')

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
    $GREEN_FOOTER = '#8b9f6f';
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1200px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    {{-- HEADER hero bar (konsisten dgn halaman admin lain) --}}
    <x-admin.page-header title="Buat Template MCU Baru" description="Tentukan format dan isi default surat undangan MCU.">
      <a href="{{ route('admin.mcu-templates.index') }}" class="ph-action">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar
      </a>
    </x-admin.page-header>

    @if(session('ok'))
      <div class="px-4 py-3 border rounded-2xl border-emerald-200 bg-emerald-50 text-emerald-700">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
      <div class="px-4 py-3 border rounded-2xl border-rose-200 bg-rose-50 text-rose-800">
        <div class="font-semibold">Periksa kembali isian berikut:</div>
        <ul class="mt-1 ml-5 text-sm list-disc">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('admin.mcu-templates.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- SECTION 1: INFORMASI TEMPLATE -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600">📋 Informasi Template</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Template (Untuk Admin)</label>
                        <input type="text" name="name" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition font-medium" placeholder="Contoh: Template Jakarta Pre-Employment" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: HEADER (Logo & Info Penerbitan) -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600">📄 Header Surat</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Perusahaan (Logo)</label>
                        <input type="text" name="company_name" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="ANDALAN">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kota Penerbitan</label>
                        <input type="text" name="city" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Jakarta">
                    </div>
                </div>
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <label class="block text-sm font-semibold text-red-800 mb-2">🚨 Nama Project (Kotak Merah)</label>
                    <input type="text" name="project_name" class="w-full px-4 py-2.5 bg-white border border-red-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition font-bold" placeholder="PROJECT">
                </div>
            </div>
        </div>

        <!-- SECTION 3: PENERIMA & SUBJECT -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600">✉️ Penerima & Subject</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Vendor Penerima</label>
                        <input type="text" name="vendor_name" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Nama Klinik / RS">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Subject / Hal</label>
                        <input type="text" name="subject" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Medical Check Up – Pre Employee">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Vendor Penerima</label>
                    <textarea name="vendor_address" rows="2" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Alamat lengkap vendor mcu"></textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 4: ISI SURAT -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600">📝 Isi Surat</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Text "For" (e.g. Pre-Employment)</label>
                        <input type="text" name="for_text" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Pre-Employment">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama BU (PT. ...)</label>
                        <input type="text" name="bu_name" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="PT. Andalan Artha Primanusa">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Matrix MCU PT ...</label>
                        <input type="text" name="matrix_owner" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Andalan Artha Primanusa">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Package MCU</label>
                        <input type="text" name="package" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Paket Standard">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Catatan (Bullet Points)</label>
                    <textarea name="notes" rows="4" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition font-mono text-sm" placeholder="1. Bagi kandidat berusia > 40 tahun, diwajibkan menjalani pemeriksaan treadmill.&#10;2. Mohon cocokan KTP asli dengan identitas kandidat yang akan diperiksa."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email Penerima Hasil MCU (Per Baris)</label>
                    <textarea name="result_emails" rows="4" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition font-mono text-sm" placeholder="email1@pt-aap.com&#10;email2@pt-aap.com&#10;email3@pt-aap.com"></textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 5: TANDA TANGAN -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600">✍️ Penanda Tangan</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Penanda Tangan</label>
                        <input type="text" name="signer_name" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Roy Hansen C. Saragih">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan Penanda Tangan</label>
                        <input type="text" name="signer_title" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="General Manager">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 6: FOOTER (GREEN SECTION) -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b border-slate-100" style="background: {{ $GREEN_FOOTER }}">
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">🏢 Bagian Footer (Hijau)</h2>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Perusahaan Footer</label>
                    <input type="text" name="footer_company_name" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="PT. Andalan Artha Primanusa">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Perusahaan Footer</label>
                    <textarea name="footer_address" rows="2" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="Jl. Plaju No.11 Kebon Melati, Tanah Abang Jakarta Pusat 10230 DKI Jakarta – Indonesia"></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email Footer</label>
                        <input type="email" name="footer_email" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="corporatesecretary@andalan-nusantara.com">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Website Footer</label>
                        <input type="text" name="footer_website" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#a77d52] focus:border-[#a77d52] transition" placeholder="www.andalan-nusantara.com">
                    </div>
                </div>
            </div>
        </div>

        <!-- STATUS -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <label class="inline-flex items-center cursor-pointer gap-3">
                <input type="checkbox" name="is_active" value="1" class="sr-only peer">
                <div class="relative w-12 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#a77d52]/20 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:start-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                <div>
                    <span class="text-sm font-semibold text-slate-700">Set sebagai template aktif</span>
                    <p class="text-xs text-slate-500 mt-0.5">Template aktif akan digunakan sebagai default ketika mengirim MCU</p>
                </div>
            </label>
        </div>

        <!-- ACTIONS -->
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('admin.mcu-templates.index') }}" class="abtn abtn-neutral">Batal</a>
            <button type="submit" class="abtn abtn-primary">Simpan Template</button>
        </div>
    </form>
</div>
@endsection
