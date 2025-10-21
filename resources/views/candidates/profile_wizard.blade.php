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
          fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
          colors: {
            brand: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' }
          }
        }
      }
    }
  </script>
  <style>
    html,body{height:100%}
    [x-cloak]{display:none!important}
    .card{border:1px solid #e5e7eb}
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
<body class="min-h-screen bg-slate-50 text-slate-900">

  {{-- HEADER --}}
  <header class="relative isolate overflow-hidden bg-gradient-to-r from-brand-700 via-brand-600 to-indigo-600">
    <div class="absolute inset-0 opacity-10">
      <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" viewBox="0 0 1200 800" fill="none">
        <g opacity=".7">
          <circle cx="100" cy="120" r="80" stroke="white"/>
          <circle cx="300" cy="60" r="40" stroke="white"/>
          <circle cx="520" cy="140" r="70" stroke="white"/>
          <circle cx="880" cy="100" r="90" stroke="white"/>
        </g>
      </svg>
    </div>
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 relative">
      <div class="flex flex-wrap items-center justify-between gap-3 text-white">
        <div class="min-w-0">
          <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M9 7V5a3 3 0 013-3h0a3 3 0 013 3v2"/>
              </svg>
            </span>
            <div>
              <h1 class="truncate text-2xl font-semibold">Lengkapi Data Kandidat</h1>
              <p class="mt-0.5 text-sm opacity-90">Posisi: <span class="font-medium">{{ $job->title }}</span></p>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('jobs.show',$job) }}" class="rounded-lg bg-white/10 px-3 py-2 text-sm font-medium ring-1 ring-inset ring-white/30 hover:bg-white/15">Kembali ke Lowongan</a>
        </div>
      </div>
    </div>
  </header>

  {{-- MAIN --}}
  <main x-data="wizard()" x-init="init()" class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">

    {{-- Alerts --}}
    @if(session('info'))
      <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800">{{ session('info') }}</div>
    @endif
    @if(session('success'))
      <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        <div class="font-semibold mb-1">Periksa kembali isian kamu:</div>
        <ul class="list-disc pl-5 text-sm">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Stepper --}}
    <div class="mb-6">
      <ol class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <li class="card rounded-2xl bg-white p-4 shadow-sm" :class="step>=1 ? 'ring-1 ring-brand-200' : ''">
          <div class="flex items-start gap-3">
            <div class="mt-1 grid h-8 w-8 place-items-center rounded-full" :class="step>1 ? 'bg-emerald-500 text-white' : 'bg-brand-600 text-white'">
              <template x-if="step>1">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </template>
              <template x-if="step<=1"><span class="font-semibold">1</span></template>
            </div>
            <div>
              <div class="text-sm font-semibold">Data Pribadi & Alamat</div>
              <p class="text-xs text-slate-500 mt-0.5">Identitas, pendidikan, dan alamat KTP/Domisili.</p>
            </div>
          </div>
        </li>
        <li class="card rounded-2xl bg-white p-4 shadow-sm" :class="step>=2 ? 'ring-1 ring-brand-200' : ''">
          <div class="flex items-start gap-3">
            <div class="mt-1 grid h-8 w-8 place-items-center rounded-full" :class="step>2 ? 'bg-emerald-500 text-white' : (step==2 ? 'bg-brand-600 text-white' : 'bg-slate-200 text-slate-600')">
              <template x-if="step>2">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </template>
              <template x-if="step<=2"><span class="font-semibold">2</span></template>
            </div>
            <div>
              <div class="text-sm font-semibold">Pelatihan & Riwayat Kerja</div>
              <p class="text-xs text-slate-500 mt-0.5">Non-formal & pengalaman kerja kamu.</p>
            </div>
          </div>
        </li>
        <li class="card rounded-2xl bg-white p-4 shadow-sm" :class="step>=3 ? 'ring-1 ring-brand-200' : ''">
          <div class="flex items-start gap-3">
            <div class="mt-1 grid h-8 w-8 place-items-center rounded-full" :class="step==3 ? 'bg-brand-600 text-white' : 'bg-slate-200 text-slate-600'">
              <span class="font-semibold">3</span>
            </div>
            <div>
              <div class="text-sm font-semibold">Referensi & Berkas</div>
              <p class="text-xs text-slate-500 mt-0.5">Kontak referensi, CV, & dokumen.</p>
            </div>
          </div>
        </li>
      </ol>
      <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-200">
        <div class="h-full bg-brand-600 transition-all" :style="`width: ${progress}%`"></div>
      </div>
    </div>

    {{-- IMPORTANT: novalidate to disable native HTML validation --}}
    <form x-ref="form"
          method="POST"
          action="{{ route('candidate.profiles.update',$job) }}"
          enctype="multipart/form-data"
          novalidate>
      @csrf

      {{-- STEP 1 --}}
      <section x-show="step===1" x-cloak class="space-y-6">
        <div class="card rounded-2xl bg-white p-5 shadow-sm">
          <h2 class="flex items-center gap-2 text-lg font-semibold">
            <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Data Pribadi
          </h2>
          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-slate-600">Nama Lengkap <span class="text-red-600">*</span></label>
              <input required name="full_name" value="{{ old('full_name',$profile->full_name) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">Nama Panggilan</label>
              <input name="nickname" value="{{ old('nickname',$profile->nickname) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">Jenis Kelamin <span class="text-red-600">*</span></label>
              <select required name="gender" class="mt-1 w-full rounded-lg border px-3 py-2">
                <option value="">—</option>
                <option value="male"   @selected(old('gender',$profile->gender)==='male')>Laki-laki</option>
                <option value="female" @selected(old('gender',$profile->gender)==='female')>Perempuan</option>
              </select>
            </div>
            <div>
              <label class="text-sm text-slate-600">Usia <span class="text-red-600">*</span></label>
              <input required type="number" min="15" max="80" name="age" value="{{ old('age',$profile->age) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">Tempat Lahir <span class="text-red-600">*</span></label>
              <input required name="birthplace" value="{{ old('birthplace',$profile->birthplace) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">Tanggal Lahir <span class="text-red-600">*</span></label>
              <input required type="date" name="birthdate" value="{{ old('birthdate', optional($profile->birthdate)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">NIK KTP <span class="text-red-600">*</span></label>
              <input required name="nik" value="{{ old('nik',$profile->nik) }}" maxlength="16" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">Email <span class="text-red-600">*</span></label>
              <input required type="email" name="email" value="{{ old('email',$profile->email ?? auth()->user()->email) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">Nomor HP <span class="text-red-600">*</span></label>
              <input required name="phone" value="{{ old('phone',$profile->phone) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">Nomor WhatsApp</label>
              <input name="whatsapp" value="{{ old('whatsapp',$profile->whatsapp) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
          </div>

          <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <div>
              <label class="text-sm text-slate-600">Pendidikan Terakhir <span class="text-red-600">*</span></label>
              @php $ed = old('last_education',$profile->last_education); @endphp
              <select required name="last_education" class="mt-1 w-full rounded-lg border px-3 py-2">
                <option value="">—</option>
                @foreach(['SD','SMP','SMA_SMK','D1','D2','D3','D4','S1','S2','S3','LAINNYA'] as $e)
                  <option value="{{ $e }}" @selected($ed===$e)>{{ $e }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="text-sm text-slate-600">Jurusan <span class="text-red-600">*</span></label>
              <input required name="education_major" value="{{ old('education_major',$profile->education_major) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-600">Sekolah/Kampus <span class="text-red-600">*</span></label>
              <input required name="education_school" value="{{ old('education_school',$profile->education_school) }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
          </div>
        </div>

        <div class="card rounded-2xl bg-white p-5 shadow-sm">
          <h2 class="flex items-center gap-2 text-lg font-semibold">
            <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7"/></svg>
            Alamat KTP & Domisili
          </h2>

          <div class="mt-4 grid gap-3">
            <label class="text-sm text-slate-600">Alamat KTP <span class="text-red-600">*</span></label>
            <textarea required name="ktp_address" class="w-full rounded-lg border px-3 py-2" rows="2">{{ old('ktp_address',$profile->ktp_address) }}</textarea>
            <div class="grid gap-3 sm:grid-cols-6">
              <input name="ktp_rt"  placeholder="RT"  value="{{ old('ktp_rt',$profile->ktp_rt) }}"  class="rounded-lg border px-3 py-2">
              <input name="ktp_rw"  placeholder="RW"  value="{{ old('ktp_rw',$profile->ktp_rw) }}"  class="rounded-lg border px-3 py-2">
              <input required name="ktp_village"  placeholder="Desa/Kelurahan" value="{{ old('ktp_village',$profile->ktp_village) }}" class="rounded-lg border px-3 py-2">
              <input required name="ktp_district" placeholder="Kecamatan" value="{{ old('ktp_district',$profile->ktp_district) }}" class="rounded-lg border px-3 py-2">
              <input required name="ktp_city"     placeholder="Kab/Kota" value="{{ old('ktp_city',$profile->ktp_city) }}" class="rounded-lg border px-3 py-2">
              <input required name="ktp_province" placeholder="Provinsi" value="{{ old('ktp_province',$profile->ktp_province) }}" class="rounded-lg border px-3 py-2">
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
              <input required name="ktp_postal_code" placeholder="Kode Pos" value="{{ old('ktp_postal_code',$profile->ktp_postal_code) }}" class="rounded-lg border px-3 py-2">
              @php $s = old('ktp_residence_status',$profile->ktp_residence_status); @endphp
              <select name="ktp_residence_status" class="rounded-lg border px-3 py-2">
                <option value="">Status Tempat Tinggal</option>
                @foreach(['OWN'=>'Milik','RENT'=>'Sewa','DORM'=>'Kost','FAMILY'=>'Keluarga','COMPANY'=>'Dinas','OTHER'=>'Lainnya'] as $k=>$v)
                  <option value="{{ $k }}" @selected($s===$k)>{{ $v }}</option>
                @endforeach
              </select>
            </div>

            <label class="mt-4 inline-flex items-center gap-2 text-sm"><input type="checkbox" x-model="sameAsKtp" class="rounded"> <span>Domisili sama dengan Alamat KTP</span></label>

            <label class="text-sm text-slate-600 mt-1">Alamat Domisili <span class="text-red-600">*</span></label>
            <textarea required name="domicile_address" x-model="domicile_address" class="w-full rounded-lg border px-3 py-2" rows="2">{{ old('domicile_address',$profile->domicile_address) }}</textarea>
            <div class="grid gap-3 sm:grid-cols-6">
              <input name="domicile_rt"  placeholder="RT"  x-model="domicile_rt"  class="rounded-lg border px-3 py-2">
              <input name="domicile_rw"  placeholder="RW"  x-model="domicile_rw"  class="rounded-lg border px-3 py-2">
              <input required name="domicile_village"  placeholder="Desa/Kelurahan" x-model="domicile_village" class="rounded-lg border px-3 py-2">
              <input required name="domicile_district" placeholder="Kecamatan" x-model="domicile_district" class="rounded-lg border px-3 py-2">
              <input required name="domicile_city"     placeholder="Kab/Kota" x-model="domicile_city" class="rounded-lg border px-3 py-2">
              <input required name="domicile_province" placeholder="Provinsi" x-model="domicile_province" class="rounded-lg border px-3 py-2">
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
              <input required name="domicile_postal_code" placeholder="Kode Pos" x-model="domicile_postal_code" class="rounded-lg border px-3 py-2">
              @php $s2 = old('domicile_residence_status',$profile->domicile_residence_status); @endphp
              <select name="domicile_residence_status" x-model="domicile_residence_status" class="rounded-lg border px-3 py-2">
                <option value="">Status Tempat Tinggal</option>
                @foreach(['OWN'=>'Milik','RENT'=>'Sewa','DORM'=>'Kost','FAMILY'=>'Keluarga','COMPANY'=>'Dinas','OTHER'=>'Lainnya'] as $k=>$v)
                  <option value="{{ $k }}" @selected($s2===$k)>{{ $v }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </section>

      {{-- STEP 2 --}}
      <section x-show="step===2" x-cloak class="space-y-6">
        <div class="card rounded-2xl bg-white p-5 shadow-sm" x-data="{ items: $store.form.trainings }">
          <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold">
              <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 016 10.882M12 14v7"/></svg>
              Pendidikan Non-Formal (Pelatihan/Sertifikasi)
            </h2>
            <button type="button" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-slate-50" @click="items.push({title:'',institution:'',period_start:'',period_end:''})">+ Tambah</button>
          </div>
          <p class="mt-1 text-xs text-slate-500">Minimal 1 pelatihan diisi.</p>
          <template x-for="(it,idx) in items" :key="idx">
            <div class="mt-4 grid gap-3 rounded-xl border p-3">
              <input class="rounded-lg border px-3 py-2" :name="`trainings[${idx}][title]`" x-model="it.title" placeholder="Nama Training/Sertifikasi *" required>
              <div class="grid gap-3 sm:grid-cols-2">
                <input class="rounded-lg border px-3 py-2" :name="`trainings[${idx}][institution]`" x-model="it.institution" placeholder="Institusi/Penyelenggara *" required>
                <div class="grid gap-3 sm:grid-cols-2">
                  <input type="date" class="rounded-lg border px-3 py-2" :name="`trainings[${idx}][period_start]`" x-model="it.period_start" required>
                  <input type="date" class="rounded-lg border px-3 py-2" :name="`trainings[${idx}][period_end]`"   x-model="it.period_end">
                </div>
              </div>
              <div class="text-right">
                <button type="button" class="text-sm text-red-600 hover:underline" @click="items.splice(idx,1)">Hapus</button>
              </div>
            </div>
          </template>
        </div>

        <div class="card rounded-2xl bg-white p-5 shadow-sm" x-data="{ items: $store.form.employments }">
          <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold">
              <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M7 7v10a2 2 0 002 2h6a2 2 0 002-2V7"/></svg>
              Riwayat Pekerjaan
            </h2>
            <button type="button" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-slate-50" @click="items.push({company:'',position_start:'',position_end:'',period_start:'',period_end:'',reason_for_leaving:'',job_description:''})">+ Tambah</button>
          </div>
          <p class="mt-1 text-xs text-slate-500">Minimal 1 riwayat pekerjaan diisi.</p>
          <template x-for="(it,idx) in items" :key="idx">
            <div class="mt-4 grid gap-3 rounded-xl border p-3">
              <input class="rounded-lg border px-3 py-2" :name="`employments[${idx}][company]`" x-model="it.company" placeholder="Nama Perusahaan *" required>
              <div class="grid gap-3 sm:grid-cols-2">
                <input class="rounded-lg border px-3 py-2" :name="`employments[${idx}][position_start]`" x-model="it.position_start" placeholder="Jabatan Awal *" required>
                <input class="rounded-lg border px-3 py-2" :name="`employments[${idx}][position_end]`"   x-model="it.position_end"   placeholder="Jabatan Akhir">
              </div>
              <div class="grid gap-3 sm:grid-cols-2">
                <input type="date" class="rounded-lg border px-3 py-2" :name="`employments[${idx}][period_start]`" x-model="it.period_start" required>
                <input type="date" class="rounded-lg border px-3 py-2" :name="`employments[${idx}][period_end]`"   x-model="it.period_end">
              </div>
              <input class="rounded-lg border px-3 py-2" :name="`employments[${idx}][reason_for_leaving]`" x-model="it.reason_for_leaving" placeholder="Alasan Berhenti">
              <textarea class="rounded-lg border px-3 py-2" rows="2" :name="`employments[${idx}][job_description]`" x-model="it.job_description" placeholder="Deskripsi Pekerjaan"></textarea>
              <div class="text-right">
                <button type="button" class="text-sm text-red-600 hover:underline" @click="items.splice(idx,1)">Hapus</button>
              </div>
            </div>
          </template>
        </div>
      </section>

      {{-- STEP 3 --}}
      <section x-show="step===3" x-cloak class="space-y-6">
        <div class="card rounded-2xl bg-white p-5 shadow-sm" x-data="{ items: $store.form.references }">
          <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold">
              <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1M12 12a5 5 0 100-10 5 5 0 000 10z"/></svg>
              Referensi (bukan keluarga)
            </h2>
            <button type="button" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-slate-50" @click="items.push({name:'',job_title:'',company:'',contact:''})">+ Tambah</button>
          </div>
          <p class="mt-1 text-xs text-slate-500">Minimal isi 3 referensi.</p>
          <template x-for="(it,idx) in items" :key="idx">
            <div class="mt-4 grid gap-3 rounded-xl border p-3 sm:grid-cols-2">
              <input class="rounded-lg border px-3 py-2" :name="`references[${idx}][name]`" x-model="it.name" placeholder="Nama *" required>
              <input class="rounded-lg border px-3 py-2" :name="`references[${idx}][job_title]`" x-model="it.job_title" placeholder="Jabatan *" required>
              <input class="rounded-lg border px-3 py-2" :name="`references[${idx}][company]`" x-model="it.company" placeholder="Perusahaan *" required>
              <input class="rounded-lg border px-3 py-2" :name="`references[${idx}][contact]`" x-model="it.contact" placeholder="Kontak (HP/Email) *" required>
              <div class="sm:col-span-2 text-right">
                <button type="button" class="text-sm text-red-600 hover:underline" @click="items.splice(idx,1)">Hapus</button>
              </div>
            </div>
          </template>
        </div>

        <div class="card rounded-2xl bg-white p-5 shadow-sm">
          <h2 class="flex items-center gap-2 text-lg font-semibold">
            <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12v7m-6 0h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Berkas
          </h2>
          <div class="mt-3 grid gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-slate-600">CV (PDF/DOC, maks 4MB) <span class="text-red-600" x-show="!hasCv">*</span></label>
              <input :required="!hasCv" type="file" name="cv" class="mt-1 w-full rounded-lg border px-3 py-2">
              @if($profile->cv_path)
                <div class="mt-1 text-xs text-slate-600">Terunggah: <a class="text-brand-700 underline" href="{{ Storage::disk('public')->url($profile->cv_path) }}" target="_blank">Lihat CV</a></div>
              @endif
            </div>
            <div>
              <label class="text-sm text-slate-600">Dokumen pendukung (bisa pilih banyak)</label>
              <input type="file" name="documents[]" multiple class="mt-1 w-full rounded-lg border px-3 py-2">
              @if(is_array($profile->documents) && count($profile->documents))
                <ul class="mt-1 text-xs list-disc pl-5">
                  @foreach($profile->documents as $d)
                    <li><a class="text-brand-700 underline" href="{{ Storage::disk('public')->url($d['path']) }}" target="_blank">{{ $d['name'] }}</a></li>
                  @endforeach
                </ul>
              @endif
            </div>
          </div>
        </div>
      </section>

      {{-- Right Action Rail --}}
      <div class="pointer-events-none fixed right-4 bottom-6 z-20 md:top-1/2 md:bottom-auto md:-translate-y-1/2">
        <div class="pointer-events-auto w-48 rounded-2xl border bg-white/95 p-3 shadow-lg backdrop-blur">
          <div class="px-1 pb-2 text-xs text-slate-600">Langkah <span class="font-semibold text-slate-900" x-text="step"></span> / 3 · <span x-text="progress+'%'"></span></div>
          <div class="space-y-2">
            <button type="button" @click.prevent="prev()" :disabled="step===1" class="w-full inline-flex items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium disabled:opacity-50">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
              <span>Prev</span>
            </button>
            <button type="button" x-show="step<3" @click.prevent="next()" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
              <span>Lanjut</span>
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            {{-- SUBMIT TERKONTROL: prevent default & submit manual saat valid --}}
            <button type="submit" x-show="step===3" @click.prevent="if (validate(3)) $refs.form.submit()" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
              <span>Simpan & Selesai</span>
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </button>
          </div>
        </div>
      </div>
    </form>

    {{-- Error bubble --}}
    <template x-if="errors.length">
      <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <div class="font-semibold">Lengkapi isian berikut sebelum lanjut:</div>
        <ul class="mt-2 list-disc pl-5">
          <template x-for="(e,i) in errors" :key="i"><li x-text="e"></li></template>
        </ul>
      </div>
    </template>

  </main>

<script>
  function wizard(){
    return {
      // === STATE ===
      step: 1,
      progress: 0,
      errors: [],
      sameAsKtp: false,
      hasCv: {{ $profile->cv_path ? 'true' : 'false' }},

      // domisili (buat copy KTP + restore draft)
      domicile_address: @json(old('domicile_address',$profile->domicile_address)),
      domicile_rt: @json(old('domicile_rt',$profile->domicile_rt)),
      domicile_rw: @json(old('domicile_rw',$profile->domicile_rw)),
      domicile_village: @json(old('domicile_village',$profile->domicile_village)),
      domicile_district: @json(old('domicile_district',$profile->domicile_district)),
      domicile_city: @json(old('domicile_city',$profile->domicile_city)),
      domicile_province: @json(old('domicile_province',$profile->domicile_province)),
      domicile_postal_code: @json(old('domicile_postal_code',$profile->domicile_postal_code)),
      domicile_residence_status: @json(old('domicile_residence_status',$profile->domicile_residence_status)),

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
            ['nik','NIK KTP'], ['email','Email'], ['phone','Nomor HP'], ['last_education','Pendidikan Terakhir'],
            ['education_major','Jurusan'], ['education_school','Sekolah/Kampus'],
            ['ktp_address','Alamat KTP'], ['ktp_village','Desa/Kelurahan (KTP)'], ['ktp_district','Kecamatan (KTP)'], ['ktp_city','Kab/Kota (KTP)'], ['ktp_province','Provinsi (KTP)'], ['ktp_postal_code','Kode Pos (KTP)'],
            ['domicile_address','Alamat Domisili'], ['domicile_village','Desa/Kelurahan (Domisili)'], ['domicile_district','Kecamatan (Domisili)'], ['domicile_city','Kab/Kota (Domisili)'], ['domicile_province','Provinsi (Domisili)'], ['domicile_postal_code','Kode Pos (Domisili)']
          ];
          req.forEach(([n,l]) => { if(!f[n] || !f[n].value?.trim()) this.errors.push(l); });
        }
        if(s===2){
          const T = Alpine.store('form').trainings || [];
          const E = Alpine.store('form').employments || [];
          if(T.length<1) this.errors.push('Minimal 1 pelatihan/sertifikasi');
          if(E.length<1) this.errors.push('Minimal 1 riwayat pekerjaan');
          T.forEach((r,i)=>{ if(!r.title?.trim()) this.errors.push(`Pelatihan #${i+1}: Nama`); if(!r.institution?.trim()) this.errors.push(`Pelatihan #${i+1}: Institusi`); if(!r.period_start?.trim()) this.errors.push(`Pelatihan #${i+1}: Tanggal Mulai`); });
          E.forEach((r,i)=>{ if(!r.company?.trim()) this.errors.push(`Pekerjaan #${i+1}: Perusahaan`); if(!r.position_start?.trim()) this.errors.push(`Pekerjaan #${i+1}: Jabatan Awal`); if(!r.period_start?.trim()) this.errors.push(`Pekerjaan #${i+1}: Tanggal Mulai`); });
        }
        if(s===3){
          const R = Alpine.store('form').references || [];
          if(R.length<3) this.errors.push('Minimal 3 referensi');
          R.forEach((r,i)=>{ if(!r.name?.trim()) this.errors.push(`Referensi #${i+1}: Nama`); if(!r.job_title?.trim()) this.errors.push(`Referensi #${i+1}: Jabatan`); if(!r.company?.trim()) this.errors.push(`Referensi #${i+1}: Perusahaan`); if(!r.contact?.trim()) this.errors.push(`Referensi #${i+1}: Kontak`); });
          if(!this.hasCv){ const f=this.$refs.form; if(!f.cv?.files?.length){ this.errors.push('CV'); } }
        }
        return this.errors.length===0;
      }
    }
  }
</script>
</body>
</html>
