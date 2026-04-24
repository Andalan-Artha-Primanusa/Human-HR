{{-- resources/views/candidates/profile_wizard.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lengkapi Data Kandidat</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Poppins', 'ui-sans-serif', 'system-ui'] },
          colors: {
            brand: { 50:'#faf5ef',100:'#f3e8db',200:'#e7d3bd',300:'#d9bc9d',400:'#c79d75',500:'#b4865d',600:'#a77d52',700:'#8b5e3c',800:'#754e34',900:'#5f412d' }
          }
        }
      }
    }
  </script>
  <style>
    html,body{height:100%}
    [x-cloak]{display:none!important}
    .card{border:1px solid #e2d6c8; background:#fff; box-shadow:0 8px 24px -18px rgba(80,58,38,.35)}
    main input, main select, main textarea{
      border-color:#dccfbe!important;
      border-radius:.75rem!important;
      background:#fff;
      transition:border-color .15s ease, box-shadow .15s ease;
    }
    main input:focus, main select:focus, main textarea:focus{
      outline:none;
      border-color:#a77d52!important;
      box-shadow:0 0 0 3px rgba(167,125,82,.18);
    }
  </style>
  {{-- Alpine --}}
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  {{-- Seed Alpine Store dari server (old()/profile) --}}
  <script>
    document.addEventListener('alpine:init', () => {
      const seed = {
        trainings: @json(old('trainings', $profile->trainings ?? [])),
        employments: @json(old('employments', $profile->employments ?? [])),
        references: @json(old('references', $profile->references ?? [])),
      };
      const normArr = (v) => Array.isArray(v) ? v : [];
      Alpine.store('form', {
        trainings:  normArr(seed.trainings),
        employments:normArr(seed.employments),
        references: normArr(seed.references),
      });
    });
  </script>
</head>
<body x-data="wizard()" x-init="init()" class="min-h-screen text-slate-900 bg-[linear-gradient(180deg,_#f8f5f1,_#fefcf9)]">
  <!-- DEBUG: tampilkan isi errors[] -->
  <span x-text="JSON.stringify(errors)" style="position:fixed;top:0;left:0;z-index:99999;background:#fff1;border:1px solid #ccc;padding:2px 8px;font-size:12px;"></span>

  {{-- HEADER --}}
  <header class="relative overflow-hidden isolate bg-gradient-to-r from-brand-700 via-brand-600 to-brand-800">
    <div class="absolute inset-0 opacity-10">
      <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" viewBox="0 0 1200 800" fill="none">
        <g opacity=".7">
          <circle cx="100" cy="120" r="80" stroke="white"/>
          <circle cx="300" cy="60" r="40" stroke="white"/>
          <circle cx="520" cy="140" r="70" stroke="white"/>
          <circle cx="880" cy="100" r="90" stroke="white"/>
        </g>
      </svg>
    </div>
    <div class="relative max-w-6xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
      <div class="flex flex-wrap items-center justify-between gap-3 text-white">
        <div class="min-w-0">
          <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 ring-1 ring-white/20">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M9 7V5a3 3 0 013-3h0a3 3 0 013 3v2"/>
              </svg>
            </span>
            <div>
              <h1 class="text-2xl font-semibold truncate">Lengkapi Data Kandidat</h1>
              <p class="mt-0.5 text-sm opacity-90">Posisi: <span class="font-medium">{{ $job->title }}</span></p>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('jobs.show', $job) }}" class="px-3 py-2 text-sm font-medium rounded-lg bg-white/10 ring-1 ring-inset ring-white/30 hover:bg-white/15">Kembali ke Lowongan</a>
        </div>
      </div>
    </div>
  </header>

  {{-- MAIN --}}
  <main class="max-w-6xl px-4 py-8 mx-auto sm:px-6 lg:px-8">

    {{-- Alerts --}}
    @if(session('info'))
          <div class="px-4 py-3 mb-4 border rounded-xl border-brand-200 bg-brand-50 text-brand-800">{{ session('info') }}</div>
    @endif
    @if(session('success'))
          <div class="px-4 py-3 mb-4 border rounded-xl border-brand-200 bg-brand-50 text-brand-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
          <div class="px-4 py-3 mb-4 border rounded-xl border-brand-200 bg-[#fbf3ea] text-brand-900">
            <div class="mb-1 font-semibold">Periksa kembali isian kamu:</div>
            <ul class="pl-5 text-sm list-disc">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
    @endif

    {{-- Stepper --}}
    <div class="mb-6">
      <ol class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <li class="p-4 bg-white shadow-sm card rounded-2xl" :class="step>=1 ? 'ring-1 ring-brand-200' : ''">
          <div class="flex items-start gap-3">
            <div class="grid w-8 h-8 mt-1 rounded-full place-items-center" :class="step>1 ? 'bg-brand-700 text-white' : 'bg-brand-600 text-white'">
              <template x-if="step>1">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </template>
              <template x-if="step<=1"><span class="font-semibold">1</span></template>
            </div>
            <div>
              <div class="text-sm font-semibold">Data Pribadi & Alamat</div>
              <p class="text-xs text-slate-500 mt-0.5">Identitas, pendidikan, dan alamat KTP/Domisili.</p>
            </div>
          </div>
        </li>
        <li class="p-4 bg-white shadow-sm card rounded-2xl" :class="step>=2 ? 'ring-1 ring-brand-200' : ''">
          <div class="flex items-start gap-3">
            <div class="grid w-8 h-8 mt-1 rounded-full place-items-center" :class="step>2 ? 'bg-brand-700 text-white' : (step==2 ? 'bg-brand-600 text-white' : 'bg-slate-200 text-slate-600')">
              <template x-if="step>2">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </template>
              <template x-if="step<=2"><span class="font-semibold">2</span></template>
            </div>
            <div>
              <div class="text-sm font-semibold">Pelatihan & Riwayat Kerja</div>
              <p class="text-xs text-slate-500 mt-0.5">Non-formal & pengalaman kerja kamu.</p>
            </div>
          </div>
        </li>
        <li class="p-4 bg-white shadow-sm card rounded-2xl" :class="step>=3 ? 'ring-1 ring-brand-200' : ''">
          <div class="flex items-start gap-3">
            <div class="grid w-8 h-8 mt-1 rounded-full place-items-center" :class="step==3 ? 'bg-brand-600 text-white' : 'bg-slate-200 text-slate-600'">
              <span class="font-semibold">3</span>
            </div>
            <div>
              <div class="text-sm font-semibold">Referensi & Berkas</div>
              <p class="text-xs text-slate-500 mt-0.5">Kontak referensi, CV, & dokumen.</p>
            </div>
          </div>
        </li>
      </ol>
      <div class="w-full h-2 mt-4 overflow-hidden rounded-full bg-slate-200">
        <div class="h-full transition-all bg-brand-600" :style="`width: ${progress}%`"></div>
      </div>
    </div>

    {{-- IMPORTANT: novalidate to disable native HTML validation --}}
    <form x-ref="form"
          method="POST"
          action="{{ route('candidate.profiles.update', $job) }}"
          enctype="multipart/form-data"
          novalidate>
      @csrf

      {{-- STEP 1 --}}
      <section x-show="step===1" x-cloak class="space-y-6">
        <div class="p-5 bg-white shadow-sm card rounded-2xl">
          <h2 class="flex items-center gap-2 text-lg font-semibold">
            <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Data Pribadi
          </h2>
          <div class="grid gap-4 mt-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-slate-600">Nama Lengkap <span class="text-red-600">*</span></label>
              <input required name="full_name" value="{{ old('full_name', $profile->full_name) }}" class="w-full px-3 py-2 mt-1 border rounded-lg">
            </div>
            <div>
              <label class="text-sm text-slate-600">Pilih POH (Tempat Penempatan) <span class="text-red-600">*</span></label>
              <select required name="poh_id" class="w-full px-3 py-2 mt-1 border rounded-lg">
                <option value="">— Pilih POH —</option>
                @foreach($pohs as $poh)
                  <option value="{{ $poh->id }}" @selected(old('poh_id', $profile->poh_id ?? null) == $poh->id)>{{ $poh->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="text-sm text-slate-600">Nama Panggilan</label>
              <input name="nickname" value="{{ old('nickname', $profile->nickname) }}" class="w-full px-3 py-2 mt-1 border rounded-lg">
            </div>
            <div>
              <label class="text-sm text-slate-600">Jenis Kelamin <span class="text-red-600">*</span></label>
              <select required name="gender" class="w-full px-3 py-2 mt-1 border rounded-lg">
                <option value="">—</option>
                <option value="male"   @selected(old('gender', $profile->gender) === 'male')>Laki-laki</option>
                <option value="female" @selected(old('gender', $profile->gender) === 'female')>Perempuan</option>
              </select>
            </div>
            <div>
              <label class="text-sm text-slate-600">Tempat Lahir <span class="text-red-600">*</span></label>
              <input required name="birthplace" value="{{ old('birthplace', $profile->birthplace) }}" class="w-full px-3 py-2 mt-1 border rounded-lg">
            </div>
            <div>
              <label class="text-sm text-slate-600">Tanggal Lahir <span class="text-red-600">*</span></label>
              <input required type="date" name="birthdate" value="{{ old('birthdate', optional($profile->birthdate)->format('Y-m-d')) }}" class="w-full px-3 py-2 mt-1 border rounded-lg" @input="const ageField = $el.form.elements['age']; if(ageField){ const val = $el.value; if(val){ const d = new Date(val); const now = new Date(); let age = now.getFullYear() - d.getFullYear(); const m = now.getMonth() - d.getMonth(); if(m < 0 || (m === 0 && now.getDate() < d.getDate())) age--; ageField.value = age; } }">
            </div>
            <div>
              <label class="text-sm text-slate-600">Usia <span class="text-red-600">*</span></label>
              <input required type="number" min="15" max="80" name="age" value="{{ old('age', $profile->age) }}" class="w-full px-3 py-2 mt-1 border rounded-lg" readonly>
            </div>
            <div>
              <label class="text-sm text-slate-600">NIK KTP <span class="text-red-600">*</span></label>
              <input required name="nik" value="{{ old('nik', $profile->nik) }}" maxlength="16" class="w-full px-3 py-2 mt-1 border rounded-lg">
            </div>
            <div>
              <label class="text-sm text-slate-600">Email <span class="text-red-600">*</span></label>
              <input required type="email" name="email" value="{{ old('email', $profile->email ?? auth()->user()->email) }}" class="w-full px-3 py-2 mt-1 border rounded-lg">
            </div>
            <div>
              <label class="text-sm text-slate-600">Nomor HP <span class="text-red-600">*</span></label>
              <input required name="phone" pattern="[0-9]{12,13}" maxlength="13" minlength="12" value="{{ old('phone', $profile->phone) }}" class="w-full px-3 py-2 mt-1 border rounded-lg" title="Nomor HP harus 12-13 digit angka" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            </div>
            <div>
              <label class="text-sm text-slate-600">Nomor WhatsApp</label>
              <input name="whatsapp" value="{{ old('whatsapp', $profile->whatsapp) }}" class="w-full px-3 py-2 mt-1 border rounded-lg">
            </div>
          </div>

          <div class="grid gap-4 mt-4 sm:grid-cols-3">
            <div>
              <label class="text-sm text-slate-600">Pendidikan Terakhir</label>
              @php $ed = old('last_education', $profile->last_education); @endphp
              <select name="last_education" id="last_education" class="w-full px-3 py-2 mt-1 border rounded-lg" onchange="document.getElementById('sma-smk-group').style.display = this.value === 'SMA_SMK' ? 'flex' : 'none'; document.getElementById('lainnya-group').style.display = this.value === 'LAINNYA' ? 'block' : 'none';">
                <option value="">—</option>
                @foreach(['SD', 'SMP', 'SMA_SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3', 'LAINNYA'] as $e)
                      <option value="{{ $e }}" @selected($ed === $e)>{{ $e }}</option>
                @endforeach
              </select>
            </div>
            <div id="sma-smk-group" class="gap-2 mt-2" style="display: {{ (old('last_education', $profile->last_education) === 'SMA_SMK') ? 'flex' : 'none' }};">
              <div class="flex items-center gap-2">
                <label class="text-sm text-slate-600">Jenis</label>
                @php $jenis = old('sma_smk_type', $profile->extras['sma_smk_type'] ?? 'SMA'); @endphp
                <select name="sma_smk_type" class="px-3 py-2 border rounded-lg">
                  <option value="SMA" @selected($jenis === 'SMA')>SMA</option>
                  <option value="SMK" @selected($jenis === 'SMK')>SMK</option>
                </select>
              </div>
              <div class="flex-1">
                <label class="text-sm text-slate-600">Nama Sekolah</label>
                <input name="sma_smk_school" value="{{ old('sma_smk_school', $profile->extras['sma_smk_school'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg">
              </div>
            </div>
            <div id="lainnya-group" class="gap-2 mt-2" style="display: {{ (old('last_education', $profile->last_education) === 'LAINNYA') ? 'block' : 'none' }};">
              <label class="text-sm text-slate-600">Nama Pendidikan Lainnya</label>
              <input name="other_education" value="{{ old('other_education', $profile->extras['other_education'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
              <label class="text-sm text-slate-600">Jurusan</label>
              <input name="education_major" value="{{ old('education_major', $profile->education_major) }}" class="w-full px-3 py-2 mt-1 border rounded-lg">
            </div>
            <div>
              <label class="text-sm text-slate-600">Sekolah/Kampus</label>
              <input name="education_school" value="{{ old('education_school', $profile->education_school) }}" class="w-full px-3 py-2 mt-1 border rounded-lg">
            </div>
          </div>
        </div>

        <div class="p-5 bg-white shadow-sm card rounded-2xl">
          <h2 class="flex items-center gap-2 text-lg font-semibold">
            <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7"/></svg>
            Alamat KTP & Domisili
          </h2>

          <div class="grid gap-3 mt-4">
            <label class="text-sm text-slate-600">Alamat KTP <span class="text-red-600">*</span></label>
            <textarea required name="ktp_address" class="w-full px-3 py-2 border rounded-lg" rows="2">{{ old('ktp_address', $profile->ktp_address) }}</textarea>
            <div class="grid gap-3 sm:grid-cols-6">
              <input name="ktp_rt"  placeholder="RT"  value="{{ old('ktp_rt', $profile->ktp_rt) }}"  class="px-3 py-2 border rounded-lg">
              <input name="ktp_rw"  placeholder="RW"  value="{{ old('ktp_rw', $profile->ktp_rw) }}"  class="px-3 py-2 border rounded-lg">
              <input required name="ktp_village"  placeholder="Desa/Kelurahan" value="{{ old('ktp_village', $profile->ktp_village) }}" class="px-3 py-2 border rounded-lg">
              <input required name="ktp_district" placeholder="Kecamatan" value="{{ old('ktp_district', $profile->ktp_district) }}" class="px-3 py-2 border rounded-lg">
              <input required name="ktp_city"     placeholder="Kab/Kota" value="{{ old('ktp_city', $profile->ktp_city) }}" class="px-3 py-2 border rounded-lg">
              <input required name="ktp_province" placeholder="Provinsi" value="{{ old('ktp_province', $profile->ktp_province) }}" class="px-3 py-2 border rounded-lg">
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
              <input required name="ktp_postal_code" placeholder="Kode Pos" value="{{ old('ktp_postal_code', $profile->ktp_postal_code) }}" class="px-3 py-2 border rounded-lg">
              @php $s = old('ktp_residence_status', $profile->ktp_residence_status); @endphp
              <select name="ktp_residence_status" class="px-3 py-2 border rounded-lg">
                <option value="">Status Tempat Tinggal</option>
                @foreach(['OWN' => 'Milik Sendiri', 'RENTAL' => 'Sewa', 'DORM' => 'Kost', 'FAMILY' => 'Keluarga', 'COMPANY' => 'Dinas', 'OTHER' => 'Lainnya'] as $k => $v)
                      <option value="{{ $k }}" @selected($s === $k)>{{ $v }}</option>
                @endforeach
              </select>
            </div>

            <label class="inline-flex items-center gap-2 mt-4 text-sm"><input type="checkbox" x-model="sameAsKtp" class="rounded"> <span>Domisili sama dengan Alamat KTP</span></label>

            <label class="mt-1 text-sm text-slate-600">Alamat Domisili <span class="text-red-600">*</span></label>
            <textarea required name="domicile_address" x-model="domicile_address" class="w-full px-3 py-2 border rounded-lg" rows="2">{{ old('domicile_address', $profile->domicile_address) }}</textarea>
            <div class="grid gap-3 sm:grid-cols-6">
              <input name="domicile_rt"  placeholder="RT"  x-model="domicile_rt"  class="px-3 py-2 border rounded-lg">
              <input name="domicile_rw"  placeholder="RW"  x-model="domicile_rw"  class="px-3 py-2 border rounded-lg">
              <input required name="domicile_village"  placeholder="Desa/Kelurahan" x-model="domicile_village" class="px-3 py-2 border rounded-lg">
              <input required name="domicile_district" placeholder="Kecamatan" x-model="domicile_district" class="px-3 py-2 border rounded-lg">
              <input required name="domicile_city"     placeholder="Kab/Kota" x-model="domicile_city" class="px-3 py-2 border rounded-lg">
              <input required name="domicile_province" placeholder="Provinsi" x-model="domicile_province" class="px-3 py-2 border rounded-lg">
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
              <input required name="domicile_postal_code" placeholder="Kode Pos" x-model="domicile_postal_code" class="px-3 py-2 border rounded-lg">
              @php $s2 = old('domicile_residence_status', $profile->domicile_residence_status); @endphp
              <select name="domicile_residence_status" x-model="domicile_residence_status" class="px-3 py-2 border rounded-lg">
                <option value="">Status Tempat Tinggal</option>
                @foreach(['OWN' => 'Milik', 'RENT' => 'Sewa', 'DORM' => 'Kost', 'FAMILY' => 'Keluarga', 'COMPANY' => 'Dinas', 'OTHER' => 'Lainnya'] as $k => $v)
                      <option value="{{ $k }}" @selected($s2 === $k)>{{ $v }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </section>

      {{-- STEP 2 --}}
      <section x-show="step===2" x-cloak class="space-y-6">
        <div class="p-5 bg-white shadow-sm card rounded-2xl" x-data="{ items: $store.form.trainings }">
          <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold">
              <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 016 10.882M12 14v7"/></svg>
              Pendidikan Non-Formal (Pelatihan/Sertifikasi)
            </h2>
            <button type="button" class="rounded-xl border border-brand-200 px-3 py-1.5 text-sm text-brand-800 hover:bg-brand-50" @click="items.push({title:'',institution:'',period_start:'',period_end:''})">+ Tambah</button>
          </div>
          <p class="mt-1 text-xs text-slate-500">Opsional.</p>
          <template x-for="(it,idx) in items" :key="idx">
            <div class="grid gap-3 p-3 mt-4 border rounded-xl">
              <input class="px-3 py-2 border rounded-lg" :name="`trainings[${idx}][title]`" x-model="it.title" placeholder="Nama Training/Sertifikasi">
              <div class="grid gap-3 sm:grid-cols-2">
                <input class="px-3 py-2 border rounded-lg" :name="`trainings[${idx}][institution]`" x-model="it.institution" placeholder="Institusi/Penyelenggara">
                <div class="grid gap-3 sm:grid-cols-2">
                  <input type="date" class="px-3 py-2 border rounded-lg" :name="`trainings[${idx}][period_start]`" x-model="it.period_start">
                  <input type="date" class="px-3 py-2 border rounded-lg" :name="`trainings[${idx}][period_end]`"   x-model="it.period_end">
                </div>
              </div>
              <div class="text-right">
                <button type="button" class="text-sm text-brand-700 hover:underline" @click="items.splice(idx,1)">Hapus</button>
              </div>
            </div>
          </template>
        </div>

        <div class="p-5 bg-white shadow-sm card rounded-2xl" x-data="{ items: $store.form.employments }">
          <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold">
              <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M7 7v10a2 2 0 002 2h6a2 2 0 002-2V7"/></svg>
              Riwayat Pekerjaan
            </h2>
            <button type="button" class="rounded-xl border border-brand-200 px-3 py-1.5 text-sm text-brand-800 hover:bg-brand-50" @click="items.push({company:'',position_start:'',position_end:'',period_start:'',period_end:'',reason_for_leaving:'',job_description:''})">+ Tambah</button>
          </div>
          <p class="mt-1 text-xs text-slate-500">Minimal 1 riwayat pekerjaan diisi.</p>
          <template x-for="(it,idx) in items" :key="idx">
            <div class="grid gap-3 p-3 mt-4 border rounded-xl">
              <input class="px-3 py-2 border rounded-lg" :name="`employments[${idx}][company]`" x-model="it.company" placeholder="Nama Perusahaan *" required>
              <div class="grid gap-3 sm:grid-cols-2">
                <input class="px-3 py-2 border rounded-lg" :name="`employments[${idx}][position_start]`" x-model="it.position_start" placeholder="Jabatan Awal *" required>
                <input class="px-3 py-2 border rounded-lg" :name="`employments[${idx}][position_end]`"   x-model="it.position_end"   placeholder="Jabatan Akhir">
              </div>
              <div class="grid gap-3 sm:grid-cols-2">
                <input type="date" class="px-3 py-2 border rounded-lg" :name="`employments[${idx}][period_start]`" x-model="it.period_start" required>
                <input type="date" class="px-3 py-2 border rounded-lg" :name="`employments[${idx}][period_end]`"   x-model="it.period_end">
              </div>
              <input class="px-3 py-2 border rounded-lg" :name="`employments[${idx}][reason_for_leaving]`" x-model="it.reason_for_leaving" placeholder="Alasan Berhenti">
              <textarea class="px-3 py-2 border rounded-lg" rows="2" :name="`employments[${idx}][job_description]`" x-model="it.job_description" placeholder="Deskripsi Pekerjaan"></textarea>
              <div class="text-right">
                <button type="button" class="text-sm text-brand-700 hover:underline" @click="items.splice(idx,1)">Hapus</button>
              </div>
            </div>
          </template>
        </div>
      </section>

      {{-- STEP 3 --}}
      <section x-show="step===3" x-cloak class="space-y-6">
        <div class="p-5 bg-white shadow-sm card rounded-2xl">
  <h2 class="flex items-center gap-2 text-lg font-semibold">
    <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-3.314 0-6 2.239-6 5v5h12v-5c0-2.761-2.686-5-6-5z"/>
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4a4 4 0 110 8 4 4 0 010-8z"/>
    </svg>
    Informasi Tambahan
  </h2>

  <div class="grid gap-4 mt-4 sm:grid-cols-2">

    {{-- Gaji Saat Ini --}}
    <div>
      <label class="text-sm text-slate-600">Gaji Saat Ini</label>
      <input
        type="text"
        inputmode="numeric"
        name="current_salary"
        class="w-full px-3 py-2 mt-1 border rounded-lg format-currency"
        placeholder="Contoh: 5.000.000"
        value="{{ old('current_salary', $profile->current_salary ? number_format($profile->current_salary, 0, ',', '.') : '') }}"
        autocomplete="off"
      >
    </div>

    {{-- Gaji Diharapkan --}}
    <div>
      <label class="text-sm text-slate-600">Gaji Yang Diharapkan</label>
      <input
        type="text"
        inputmode="numeric"
        name="expected_salary"
        class="w-full px-3 py-2 mt-1 border rounded-lg format-currency"
        placeholder="Contoh: 7.000.000"
        value="{{ old('expected_salary', $profile->expected_salary ? number_format($profile->expected_salary, 0, ',', '.') : '') }}"
        autocomplete="off"
      >
    </div>
  <script>
    // Format input currency (titik ribuan, tanpa nol otomatis)
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('input.format-currency').forEach(function(input) {
        input.addEventListener('input', function(e) {
          let value = this.value.replace(/[^\d]/g, '');
          if (value) {
            this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
          } else {
            this.value = '';
          }
        });
      });
    });
  </script>

    {{-- Fasilitas --}}
    <div class="sm:col-span-2">
      <label class="text-sm text-slate-600">Fasilitas Yang Diharapkan</label>
      <textarea
        name="expected_facilities"
        rows="3"
        class="w-full px-3 py-2 mt-1 border rounded-lg"
        placeholder="Mess, transport, asuransi, dll"
      >{{ old('expected_facilities', $profile->expected_facilities) }}</textarea>
    </div>

    {{-- Tanggal Siap Bekerja --}}
    <div>
      <label class="text-sm text-slate-600">Tanggal Siap Mulai Bekerja</label>
      <input
        type="date"
        name="available_start_date"
        class="w-full px-3 py-2 mt-1 border rounded-lg"
        value="{{ old('available_start_date', optional($profile->available_start_date)->format('Y-m-d')) }}"
      >
    </div>

    {{-- Motivasi Kerja --}}
    <div class="sm:col-span-2">
      <label class="text-sm text-slate-600">Motivasi Kerja di Andalan Group</label>
      <textarea
        name="work_motivation"
        rows="4"
        class="w-full px-3 py-2 mt-1 border rounded-lg"
      >{{ old('work_motivation', $profile->work_motivation) }}</textarea>
    </div>

    {{-- Riwayat Kesehatan --}}
    <div class="sm:col-span-2">
      <label class="text-sm text-slate-600">Riwayat Penyakit / Operasi</label>
      <textarea
        name="medical_history"
        rows="3"
        class="w-full px-3 py-2 mt-1 border rounded-lg"
      >{{ old('medical_history', $profile->medical_history) }}</textarea>
    </div>

    {{-- Medical Checkup --}}
    <div class="sm:col-span-2">
      <label class="text-sm text-slate-600">Pemeriksaan Kesehatan Terakhir</label>
      <input
        type="text"
        name="last_medical_checkup"
        class="w-full px-3 py-2 mt-1 border rounded-lg"
        placeholder="Contoh: Jan 2024 - RS Siloam"
        value="{{ old('last_medical_checkup', $profile->last_medical_checkup) }}"
      >
    </div>

  </div>
</div>

      <div class="p-5 bg-white shadow-sm card rounded-2xl" x-data="{ items: $store.form.references }">
          <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold">
              <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1M12 12a5 5 0 100-10 5 5 0 000 10z"/></svg>
              Referensi (bukan keluarga, 1 orang saja)
            </h2>
          </div>
          <p class="mt-1 text-xs text-slate-500">Isi minimal 1 referensi.</p>
          <template x-for="(it,idx) in items.slice(0,1)" :key="idx">
            <div class="grid gap-3 p-3 mt-4 border rounded-xl sm:grid-cols-2">
              <input class="px-3 py-2 border rounded-lg" :name="`references[${idx}][name]`" x-model="it.name" placeholder="Nama *" required>
              <input class="px-3 py-2 border rounded-lg" :name="`references[${idx}][job_title]`" x-model="it.job_title" placeholder="Jabatan *" required>
              <input class="px-3 py-2 border rounded-lg" :name="`references[${idx}][company]`" x-model="it.company" placeholder="Perusahaan *" required>
              <input class="px-3 py-2 border rounded-lg" :name="`references[${idx}][contact]`" x-model="it.contact" placeholder="Kontak (HP/Email) *" required>
            </div>
          </template>
        </div>

        <div class="p-5 bg-white shadow-sm card rounded-2xl">
          <h2 class="flex items-center gap-2 text-lg font-semibold">
            <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12v7m-6 0h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            CV & Certificate (PDF, maks 4MB)
          </h2>
          <div class="grid gap-4 mt-3 sm:grid-cols-2">
            <div>
              <label class="text-sm text-slate-600">CV (PDF, maks 4MB) <span class="text-red-600" x-show="!hasCv">*</span></label>
              <input :required="!hasCv" type="file" name="cv" accept="application/pdf" class="w-full px-3 py-2 mt-1 border rounded-lg">
              @if($profile->cv_path)
                <div class="mt-1 text-xs text-slate-600">Terunggah: <a class="underline text-brand-700" href="{{ asset('storage/' . ltrim($profile->cv_path, '/')) }}" target="_blank">Lihat CV</a></div>
              @endif
            </div>
            <div>
              <label class="text-sm text-slate-600">Dokumen pendukung (PDF only, bisa pilih banyak)</label>
              <input type="file" name="documents[]" multiple accept="application/pdf" class="w-full px-3 py-2 mt-1 border rounded-lg">
              @if(is_array($profile->documents) && count($profile->documents))
                <ul class="pl-5 mt-1 text-xs list-disc">
                  @foreach($profile->documents as $d)
                    <li><a class="underline text-brand-700" href="{{ asset('storage/' . ltrim($d['path'], '/')) }}" target="_blank">{{ $d['name'] }}</a></li>
                  @endforeach
                </ul>
              @endif
            </div>
          </div>
        </div>
      </section>

      {{-- Right Action Rail --}}
      <div class="fixed z-20 pointer-events-none inset-x-0 bottom-0 p-3 md:inset-x-auto md:p-0 md:right-4 md:top-1/2 md:-translate-y-1/2">
        <div class="w-full max-w-sm mx-auto md:mx-0 md:w-48 p-3 border shadow-[0_-4px_20px_rgba(0,0,0,0.1)] md:shadow-lg pointer-events-auto rounded-2xl md:rounded-2xl rounded-b-none md:border-brand-200/70 border-brand-200 bg-white/95 backdrop-blur">
          <div class="px-1 pb-2 text-xs text-center md:text-left text-slate-600">Langkah <span class="font-semibold text-slate-900" x-text="step"></span> / 3 · <span x-text="progress+'%'"></span></div>
          <div class="space-y-2">
            <button type="button" @click.prevent="prev()" :disabled="step===1" class="inline-flex items-center justify-center w-full gap-2 px-3 py-2 text-sm font-medium border rounded-xl border-brand-200 text-brand-800 hover:bg-brand-50 disabled:opacity-50">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
              <span>Prev</span>
            </button>
            <button type="button" x-show="step<3" @click.prevent="next()" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] px-3 py-2 text-sm font-semibold text-white hover:brightness-105">
              <span>Lanjut</span>
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            {{-- SUBMIT TERKONTROL: prevent default & submit manual saat valid --}}
            <button type="submit" x-show="step===3" @click.prevent="if (validate(3)) confirmOpen=true" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] px-3 py-2 text-sm font-semibold text-white hover:brightness-105">
              <span>Simpan & Selesai</span>
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </button>
          </div>
        </div>
      </div>

      {{-- Popup konfirmasi submit --}}
      <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-40 grid p-4 place-items-center bg-black/35" @click.self="confirmOpen=false">
        <div class="w-full max-w-md p-5 bg-white border shadow-xl rounded-2xl border-brand-200">
          <h3 class="text-lg font-semibold text-slate-900">Simpan Data Kandidat?</h3>
          <p class="mt-1 text-sm text-slate-600">Pastikan semua data sudah benar. Kamu tetap bisa mengubahnya lagi nanti.</p>
          <div class="flex items-center justify-end gap-2 mt-4">
            <button type="button" class="px-4 py-2 text-sm border rounded-xl border-brand-200 text-brand-800 hover:bg-brand-50" @click="confirmOpen=false">Batal</button>
            <button type="button" class="rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] px-4 py-2 text-sm font-semibold text-white hover:brightness-105" @click="confirmOpen=false; $refs.form.submit()">Ya, Simpan</button>
          </div>
        </div>
      </div>
    </form>



</main>

  <!-- Beri jarak bawah ekstra untuk mobile agar konten tidak tertutup Action Rail -->
  <div class="h-24 md:h-0"></div>

{{-- Error Modal: kelengkapan wajib, render di akhir body agar floating di atas semua konten --}}
<template x-if="errors.length">
  <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
    <div class="relative w-full max-w-md p-6 text-center bg-white border shadow-xl rounded-2xl border-amber-300">
      <button @click="errors=[]" class="absolute top-3 right-3 text-slate-400 hover:text-slate-700" aria-label="Tutup">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
      <div class="mb-2 text-2xl font-bold text-amber-700">Lengkapi Isian Berikut</div>
      <div class="mb-4 text-sm text-slate-700">Sebelum lanjut, mohon lengkapi data berikut:</div>
      <ul class="pl-5 mb-4 text-sm text-left list-disc text-slate-700">
        <template x-for="(e,i) in errors" :key="i"><li x-text="e"></li></template>
      </ul>
      <button @click="errors=[]" class="mt-2 inline-flex items-center rounded-lg bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] px-4 py-2 text-sm font-semibold text-white">Tutup</button>
    </div>
  </div>
</template>

</body>
</html>

  </main>

<script>
  function wizard(){
    return {
      // === STATE ===
      step: 1,
      progress: 0,
      errors: [],
      confirmOpen: false,
      sameAsKtp: false,
      hasCv: {{ $profile->cv_path ? 'true' : 'false' }},

      // domisili (buat copy KTP + restore draft)
      domicile_address: @json(old('domicile_address', $profile->domicile_address)),
      domicile_rt: @json(old('domicile_rt', $profile->domicile_rt)),
      domicile_rw: @json(old('domicile_rw', $profile->domicile_rw)),
      domicile_village: @json(old('domicile_village', $profile->domicile_village)),
      domicile_district: @json(old('domicile_district', $profile->domicile_district)),
      domicile_city: @json(old('domicile_city', $profile->domicile_city)),
      domicile_province: @json(old('domicile_province', $profile->domicile_province)),
      domicile_postal_code: @json(old('domicile_postal_code', $profile->domicile_postal_code)),
      domicile_residence_status: @json(old('domicile_residence_status', $profile->domicile_residence_status)),

      // === DRAFT KEY PER USER x JOB ===
      get DRAFT_KEY(){ return `cand_wizard:{{ auth()->id() }}:{{ $job->id }}`; },

      // === LIFECYCLE ===
      init(){
        if(Alpine.store('form').trainings.length===0){
          Alpine.store('form').trainings.push({title:'',institution:'',period_start:'',period_end:''});
        }
        if(Alpine.store('form').employments.length===0){
          Alpine.store('form').employments.push({company:'',position_start:'',position_end:'',period_start:'',period_end:'',reason_for_leaving:'',job_description:''});
        }
        while(Alpine.store('form').references.length<3){
          Alpine.store('form').references.push({name:'',job_title:'',company:'',contact:''});
        }

        this.loadDraft();

        this.$watch('step', () => { this.computeProgress(); this.saveDraft(); });
        this.$watch('sameAsKtp', v => { if(v) this.copyKtpToDomisili(); });

        this.$watch(() => JSON.stringify(Alpine.store('form').trainings),  () => this.saveDraft());
        this.$watch(() => JSON.stringify(Alpine.store('form').employments),() => this.saveDraft());
        this.$watch(() => JSON.stringify(Alpine.store('form').references), () => this.saveDraft());

        this.computeProgress();
        window.addEventListener('beforeunload', () => this.saveDraft());
      },

      // === DRAFT ===
      serializeFields(){
        const fd = new FormData(this.$refs.form);
        const out = {};
        for(const [k,v] of fd.entries()){
          if (k.startsWith('trainings[') || k.startsWith('employments[') || k.startsWith('references[') || k==='cv' || k==='documents[]') continue;
          out[k] = v;
        }
        return out;
      },
      saveDraft(){
        const snapshot = {
          step: this.step,
          fields: this.serializeFields(),
          trainings: Alpine.store('form').trainings,
          employments: Alpine.store('form').employments,
          references: Alpine.store('form').references,
          ts: Date.now()
        };
        try { localStorage.setItem(this.DRAFT_KEY, JSON.stringify(snapshot)); } catch (e) {}
      },
      loadDraft(){
        try {
          const raw = localStorage.getItem(this.DRAFT_KEY);
          if(!raw) return;
          const d = JSON.parse(raw);

          if (d.step) this.step = d.step;
          if (Array.isArray(d.trainings))   Alpine.store('form').trainings   = d.trainings;
          if (Array.isArray(d.employments)) Alpine.store('form').employments = d.employments;
          if (Array.isArray(d.references))  Alpine.store('form').references  = d.references;

          if (d.fields) {
            Object.entries(d.fields).forEach(([name, val]) => {
              const el = this.$refs.form.elements.namedItem(name);
              if (!el) return;
              if (el.type === 'checkbox' || el.type === 'radio'){
                el.checked = (val === '1' || val === true || val === 'on');
              } else {
                el.value = val ?? '';
              }
            });
          }
        } catch(e) {}
      },

      // === HELPERS ===
      copyKtpToDomisili(){
        const f = this.$refs.form;
        this.domicile_address  = f.ktp_address.value;
        this.domicile_rt       = f.ktp_rt.value;
        this.domicile_rw       = f.ktp_rw.value;
        this.domicile_village  = f.ktp_village.value;
        this.domicile_district = f.ktp_district.value;
        this.domicile_city     = f.ktp_city.value;
        this.domicile_province = f.ktp_province.value;
        this.domicile_postal_code    = f.ktp_postal_code.value;
        this.domicile_residence_status = f.ktp_residence_status.value;
        this.saveDraft();
      },
      computeProgress(){ this.progress = Math.round(((this.step-1)/2)*100); },
      next(){ if(this.validate(this.step)) { this.step = Math.min(3, this.step+1); } },
      prev(){ this.step = Math.max(1, this.step-1); },

      // === VALIDATION (client-side guard antar step) ===
      validate(s){
        this.errors = [];
        if(s===1){
          const f = this.$refs.form;
          const req = [
            ['full_name','Nama Lengkap'], ['gender','Jenis Kelamin'], ['age','Usia'], ['birthplace','Tempat Lahir'], ['birthdate','Tanggal Lahir'],
            ['nik','NIK KTP'], ['email','Email'], ['phone','Nomor HP'],
            ['ktp_address','Alamat KTP'], ['ktp_village','Desa/Kelurahan (KTP)'], ['ktp_district','Kecamatan (KTP)'], ['ktp_city','Kab/Kota (KTP)'], ['ktp_province','Provinsi (KTP)'], ['ktp_postal_code','Kode Pos (KTP)'],
            ['domicile_address','Alamat Domisili'], ['domicile_village','Desa/Kelurahan (Domisili)'], ['domicile_district','Kecamatan (Domisili)'], ['domicile_city','Kab/Kota (Domisili)'], ['domicile_province','Provinsi (Domisili)'], ['domicile_postal_code','Kode Pos (Domisili)']
          ];
          req.forEach(([n,l]) => { if(!f[n] || !f[n].value?.trim()) this.errors.push(l); });
        }
        if(s===2){
          const T = Alpine.store('form').trainings || [];
          const E = Alpine.store('form').employments || [];
          if(E.length<1) this.errors.push('Minimal 1 riwayat pekerjaan');
          T.forEach((r,i)=>{ if(r.title?.trim() || r.institution?.trim() || r.period_start?.trim()){ if(!r.title?.trim()) this.errors.push(`Pelatihan #${i+1}: Nama`); if(!r.institution?.trim()) this.errors.push(`Pelatihan #${i+1}: Institusi`); if(!r.period_start?.trim()) this.errors.push(`Pelatihan #${i+1}: Tanggal Mulai`); } });
          E.forEach((r,i)=>{ if(!r.company?.trim()) this.errors.push(`Pekerjaan #${i+1}: Perusahaan`); if(!r.position_start?.trim()) this.errors.push(`Pekerjaan #${i+1}: Jabatan Awal`); if(!r.period_start?.trim()) this.errors.push(`Pekerjaan #${i+1}: Tanggal Mulai`); });
        }
        if(s===3){
          const R = Alpine.store('form').references || [];
          if(R.length<1) this.errors.push('Minimal 1 referensi');
          R.slice(0,1).forEach((r,i)=>{ if(!r.name?.trim()) this.errors.push(`Referensi #${i+1}: Nama`); if(!r.job_title?.trim()) this.errors.push(`Referensi #${i+1}: Jabatan`); if(!r.company?.trim()) this.errors.push(`Referensi #${i+1}: Perusahaan`); if(!r.contact?.trim()) this.errors.push(`Referensi #${i+1}: Kontak`); });
          if(!this.hasCv){ const f=this.$refs.form; if(!f.cv?.files?.length){ this.errors.push('CV'); } }
        }
        return this.errors.length===0;
      }
    }
  }
</script>
</body>
</html>
