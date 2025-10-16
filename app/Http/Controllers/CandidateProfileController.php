<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\CandidateProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CandidateProfileController extends Controller
{
    public function edit(Request $request, Job $job)
    {
        $user = $request->user();

        /** @var CandidateProfile $profile */
        $profile = CandidateProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['full_name' => $user->name]
        );

        $trainings   = $profile->trainings()->orderBy('order_no')->get(['title','institution','period_start','period_end'])->toArray();
        $employments = $profile->employments()->orderBy('order_no')->get(['company','position_start','position_end','period_start','period_end','reason_for_leaving','job_description'])->toArray();
        $references  = $profile->references()->orderBy('order_no')->get(['name','job_title','company','contact'])->toArray();

        return view('candidates.profile_wizard', compact('job','profile','trainings','employments','references'));
    }

    public function update(Request $request, Job $job)
    {
        $user = $request->user();

        // ---------- VALIDASI UTAMA ----------
        $data = $request->validate([
            'full_name'         => 'bail|required|string|max:190',
            'gender'            => 'bail|required|in:male,female',
            'age'               => 'bail|required|integer|between:15,80',
            'birthplace'        => 'bail|required|string|max:190',
            'birthdate'         => 'bail|required|date',
            'nik'               => 'bail|required|digits:16',
            'email'             => 'bail|required|email',
            'phone'             => 'bail|required|string|max:50',
            'whatsapp'          => 'nullable|string|max:50',

            'last_education'    => 'bail|required|string|max:30',
            'education_major'   => 'bail|required|string|max:190',
            'education_school'  => 'bail|required|string|max:190',

            'ktp_address'       => 'bail|required|string',
            'ktp_village'       => 'bail|required|string|max:190',
            'ktp_district'      => 'bail|required|string|max:190',
            'ktp_city'          => 'bail|required|string|max:190',
            'ktp_province'      => 'bail|required|string|max:190',
            'ktp_postal_code'   => 'bail|required|string|max:20',
            'ktp_rt'            => 'nullable|string|max:10',
            'ktp_rw'            => 'nullable|string|max:10',
            'ktp_residence_status' => 'nullable|in:OWN,RENT,DORM,FAMILY,COMPANY,OTHER',

            'domicile_address'     => 'bail|required|string',
            'domicile_village'     => 'bail|required|string|max:190',
            'domicile_district'    => 'bail|required|string|max:190',
            'domicile_city'        => 'bail|required|string|max:190',
            'domicile_province'    => 'bail|required|string|max:190',
            'domicile_postal_code' => 'bail|required|string|max:20',
            'domicile_rt'          => 'nullable|string|max:10',
            'domicile_rw'          => 'nullable|string|max:10',
            'domicile_residence_status' => 'nullable|in:OWN,RENT,DORM,FAMILY,COMPANY,OTHER',

            'motivation'             => 'nullable|string',
            'has_relatives'          => 'nullable|boolean',
            'relatives_detail'       => 'nullable|string|max:255',
            'worked_before'          => 'nullable|boolean',
            'worked_before_position' => 'nullable|string|max:190',
            'worked_before_duration' => 'nullable|string|max:190',
            'applied_before'         => 'nullable|boolean',
            'applied_before_position'=> 'nullable|string|max:190',
            'willing_out_of_town'    => 'nullable|boolean',
            'not_willing_reason'     => 'nullable|string|max:255',

            'cv'         => 'nullable|file|mimes:pdf,doc,docx|max:4096',
            'documents.*'=> 'nullable|file|max:4096',

            // Repeater
            'trainings'                    => 'bail|required|array|min:1',
            'trainings.*.title'            => 'required|string|max:190',
            'trainings.*.institution'      => 'required|string|max:190',
            'trainings.*.period_start'     => 'required|date',
            'trainings.*.period_end'       => 'nullable|date',

            'employments'                  => 'bail|required|array|min:1',
            'employments.*.company'        => 'required|string|max:190',
            'employments.*.position_start' => 'required|string|max:190',
            'employments.*.position_end'   => 'nullable|string|max:190',
            'employments.*.period_start'   => 'required|date',
            'employments.*.period_end'     => 'nullable|date',
            'employments.*.reason_for_leaving' => 'nullable|string|max:255',
            'employments.*.job_description'    => 'nullable|string',

            'references'               => 'bail|required|array|min:3',
            'references.*.name'        => 'required|string|max:190',
            'references.*.job_title'   => 'required|string|max:190',
            'references.*.company'     => 'required|string|max:190',
            'references.*.contact'     => 'required|string|max:190',
        ]);

        // ---------- VALIDASI MANUAL RANGE TANGGAL DI REPEATER ----------
        $errors = [];

        foreach ($request->input('trainings', []) as $i => $t) {
            $start = $t['period_start'] ?? null;
            $end   = $t['period_end']   ?? null;
            if ($start && $end && $end < $start) {
                $errors["trainings.$i.period_end"] = "Tanggal selesai pelatihan #".($i+1)." harus >= tanggal mulai.";
            }
        }

        foreach ($request->input('employments', []) as $i => $e) {
            $start = $e['period_start'] ?? null;
            $end   = $e['period_end']   ?? null;
            if ($start && $end && $end < $start) {
                $errors["employments.$i.period_end"] = "Tanggal selesai pekerjaan #".($i+1)." harus >= tanggal mulai.";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        // Helper untuk ENUM/string opsional → null jika kosong
        $nullIfBlank = function ($v) {
            return (isset($v) && $v !== '') ? $v : null;
        };

        // ---------- SIMPAN DATA DALAM TRANSAKSI ----------
        DB::transaction(function () use ($user, $data, $request, $nullIfBlank) {
            /** @var CandidateProfile $profile */
            $profile = CandidateProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['full_name' => $user->name]
            );

            // Field profil
            $profile->fill([
                'full_name'   => $data['full_name'],
                'nickname'    => $request->input('nickname'),
                'gender'      => $data['gender'],
                'age'         => (int) $data['age'],
                'birthplace'  => $data['birthplace'],
                'birthdate'   => $data['birthdate'],
                'nik'         => $data['nik'],
                'email'       => $data['email'],
                'phone'       => $data['phone'],
                'whatsapp'    => $request->input('whatsapp'),

                'last_education'   => $data['last_education'],
                'education_major'  => $data['education_major'],
                'education_school' => $data['education_school'],

                // KTP
                'ktp_address'     => $data['ktp_address'],
                'ktp_rt'          => $request->input('ktp_rt'),
                'ktp_rw'          => $request->input('ktp_rw'),
                'ktp_village'     => $data['ktp_village'],
                'ktp_district'    => $data['ktp_district'],
                'ktp_city'        => $data['ktp_city'],
                'ktp_province'    => $data['ktp_province'],
                'ktp_postal_code' => $data['ktp_postal_code'],
                'ktp_residence_status' => $nullIfBlank($request->input('ktp_residence_status')),   // penting

                // Domisili
                'domicile_address'     => $data['domicile_address'],
                'domicile_rt'          => $request->input('domicile_rt'),
                'domicile_rw'          => $request->input('domicile_rw'),
                'domicile_village'     => $data['domicile_village'],
                'domicile_district'    => $data['domicile_district'],
                'domicile_city'        => $data['domicile_city'],
                'domicile_province'    => $data['domicile_province'],
                'domicile_postal_code' => $data['domicile_postal_code'],
                'domicile_residence_status' => $nullIfBlank($request->input('domicile_residence_status')), // penting

                // Pernyataan
                'motivation'              => $request->input('motivation'),
                'has_relatives'           => $request->boolean('has_relatives'),
                'relatives_detail'        => $request->input('relatives_detail'),
                'worked_before'           => $request->boolean('worked_before'),
                'worked_before_position'  => $request->input('worked_before_position'),
                'worked_before_duration'  => $request->input('worked_before_duration'),
                'applied_before'          => $request->boolean('applied_before'),
                'applied_before_position' => $request->input('applied_before_position'),
                'willing_out_of_town'     => $request->boolean('willing_out_of_town'),
                'not_willing_reason'      => $request->input('not_willing_reason'),
            ]);

            // CV (replace)
            if ($request->hasFile('cv')) {
                $path = $request->file('cv')->store('candidates/cv', 'public');
                $profile->cv_path = $path;
            }

            // Dokumen pendukung (append)
            $docs = is_array($profile->documents ?? []) ? $profile->documents : [];
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $f) {
                    if (!$f) continue;
                    $p = $f->store('candidates/docs', 'public');
                    $docs[] = ['name' => $f->getClientOriginalName(), 'path' => $p];
                }
                $profile->documents = $docs;
            }

            $profile->save();

            // ---------- Simpan repeater (reset & insert ulang) ----------
            // Trainings
            $profile->trainings()->delete();
            foreach ((array) $request->input('trainings', []) as $i => $row) {
                if (!filled($row['title'] ?? null)) continue;
                $profile->trainings()->create([
                    'order_no'      => $i,
                    'title'         => $row['title'],
                    'institution'   => $row['institution'] ?? null,
                    'period_start'  => $row['period_start'] ?? null,
                    'period_end'    => $row['period_end'] ?? null,
                ]);
            }

            // Employments
            $profile->employments()->delete();
            foreach ((array) $request->input('employments', []) as $i => $row) {
                if (!filled($row['company'] ?? null)) continue;
                $profile->employments()->create([
                    'order_no'           => $i,
                    'company'            => $row['company'],
                    'position_start'     => $row['position_start'] ?? null,
                    'position_end'       => $row['position_end'] ?? null,
                    'period_start'       => $row['period_start'] ?? null,
                    'period_end'         => $row['period_end'] ?? null,
                    'reason_for_leaving' => $row['reason_for_leaving'] ?? null,
                    'job_description'    => $row['job_description'] ?? null,
                ]);
            }

            // References
            $profile->references()->delete();
            foreach ((array) $request->input('references', []) as $i => $row) {
                if (!filled($row['name'] ?? null)) continue;
                $profile->references()->create([
                    'order_no'  => $i,
                    'name'      => $row['name'],
                    'job_title' => $row['job_title'] ?? null,
                    'company'   => $row['company'] ?? null,
                    'contact'   => $row['contact'] ?? null,
                ]);
            }
        });

        // ---------- REDIRECT ----------
        return redirect()
            ->route('jobs.show', $job)
            ->with('success', 'Data kandidat berhasil disimpan.');
    }


    // === ADMIN: LIST CANDIDATES ===
public function adminIndex(Request $request)
{
    $q = trim((string) $request->query('q', ''));

    $profiles = CandidateProfile::query()
        ->withCount(['trainings','employments','references'])
        ->when($q !== '', function ($w) use ($q) {
            $w->where(function ($s) use ($q) {
                $s->where('full_name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('nik', 'like', "%{$q}%");
            });
        })
        ->orderBy('updated_at','desc')
        ->paginate(20)
        ->withQueryString();

    return view('admin.candidates.index', compact('profiles','q'));
}

// === ADMIN: SHOW CANDIDATE ===
public function adminShow(CandidateProfile $profile)
{
    $profile->load([
        'trainings'   => fn($q) => $q->orderBy('order_no'),
        'employments' => fn($q) => $q->orderBy('order_no'),
        'references'  => fn($q) => $q->orderBy('order_no'),
        'user',
    ]);

    return view('admin.candidates.show', compact('profile'));
}

// === ADMIN: VIEW/DOWNLOAD CV (opsional) ===
public function adminCv(CandidateProfile $profile)
{
    if (!$profile->cv_path) {
        abort(404, 'CV tidak tersedia.');
    }

    // opsi 1: redirect ke URL publik (jika disk public dipakai)
    if (Storage::disk('public')->exists($profile->cv_path)) {
        return redirect()->away(Storage::disk('public')->url($profile->cv_path));
    }

    // opsi 2: stream file (jika perlu)
    if (Storage::exists($profile->cv_path)) {
        $path = Storage::path($profile->cv_path);
        return response()->file($path);
    }

    abort(404, 'File CV tidak ditemukan.');
}

}
