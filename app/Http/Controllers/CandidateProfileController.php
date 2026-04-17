<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CandidateProfile;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CandidateProfileController extends Controller
{
    /**
     * Tampilkan wizard profil kandidat.
     */
    public function edit(Request $request, Job $job)
    {
        $user = $request->user();

        /** @var CandidateProfile $profile */
        $profile = CandidateProfile::firstOrCreate(['user_id' => $user->id], ['full_name' => $user->name]);

        // Ambil ONLY kolom yang dipakai view (hemat)
        $trainings = $profile->trainings()->orderBy('order_no')->get(['title', 'institution', 'period_start', 'period_end'])->toArray();

        $employments = $profile->employments()->orderBy('order_no')->get(['company', 'position_start', 'position_end', 'period_start', 'period_end', 'reason_for_leaving', 'job_description'])->toArray();

        $references = $profile->references()->orderBy('order_no')->get(['name', 'job_title', 'company', 'contact'])->toArray();

        // Ambil POH untuk dropdown (hanya yang aktif)
        $pohs = \App\Models\Poh::query()->orderBy('name')->get(['id', 'name']);

        return view('candidates.profile_wizard', compact('job', 'profile', 'trainings', 'employments', 'references', 'pohs'));
    }

    /**
     * Simpan perubahan profil kandidat.
     */
    public function update(Request $request, Job $job)
    {
        $user = $request->user();
        abort_if(!$user, 401);

        // Batasan jumlah supaya payload ga jumbo
        $maxTrainings = 50;
        $maxEmployments = 50;
        $maxReferences = 100;
        $maxDocuments = 20;

        // Parse gaji: string format 1.000.000 → 1000000
        $request->merge([
            'current_salary' => $this->parseCurrency($request->input('current_salary')),
            'expected_salary' => $this->parseCurrency($request->input('expected_salary')),
        ]);

        // ===== VALIDASI UTAMA =====
        $validated = $request->validate([
            'poh_id' => ['required', 'uuid', 'exists:pohs,id'],
            'full_name' => 'bail|required|string|max:190',
            'gender' => ['bail', 'required', Rule::in(['male', 'female'])],
            'age' => 'bail|required|integer|between:15,80',
            'birthplace' => 'bail|required|string|max:190',
            'birthdate' => 'bail|required|date',
            'nik' => ['bail', 'required', 'digits:16'],
            'email' => 'bail|required|email:rfc,dns',
            'phone' => [
                'bail',
                'required',
                'regex:/^[0-9]{12,13}$/',
                'max:13',
                'min:12',
            ],
            'whatsapp' => 'nullable|string|max:50',
            'last_education' => 'bail|required|string|max:30',
            'education_major' => 'bail|required|string|max:190',
            'education_school' => 'bail|required|string|max:190',
            'ktp_address' => 'bail|required|string',
            'ktp_village' => 'bail|required|string|max:190',
            'ktp_district' => 'bail|required|string|max:190',
            'ktp_city' => 'bail|required|string|max:190',
            'ktp_province' => 'bail|required|string|max:190',
            'ktp_postal_code' => 'bail|required|string|max:20',
            'ktp_rt' => 'nullable|string|max:10',
            'ktp_rw' => 'nullable|string|max:10',
            'ktp_residence_status' => ['nullable', Rule::in(['OWN', 'RENT', 'DORM', 'FAMILY', 'COMPANY', 'OTHER'])],
            'domicile_address' => 'bail|required|string',
            'domicile_village' => 'bail|required|string|max:190',
            'domicile_district' => 'bail|required|string|max:190',
            'domicile_city' => 'bail|required|string|max:190',
            'domicile_province' => 'bail|required|string|max:190',
            'domicile_postal_code' => 'bail|required|string|max:20',
            'domicile_rt' => 'nullable|string|max:10',
            'domicile_rw' => 'nullable|string|max:10',
            'domicile_residence_status' => ['nullable', Rule::in(['OWN', 'RENT', 'DORM', 'FAMILY', 'COMPANY', 'OTHER'])],
            'motivation' => 'nullable|string',
            'has_relatives' => 'nullable|boolean',
            'relatives_detail' => 'nullable|string|max:255',
            'worked_before' => 'nullable|boolean',
            'worked_before_position' => 'nullable|string|max:190',
            'worked_before_duration' => 'nullable|string|max:190',
            'applied_before' => 'nullable|boolean',
            'applied_before_position' => 'nullable|string|max:190',
            'willing_out_of_town' => 'nullable|boolean',
            'not_willing_reason' => 'nullable|string|max:255',
            // File (4MB, PDF only)
            'cv' => 'nullable|file|mimes:pdf|max:4096',
            'documents' => "nullable|array|max:{$maxDocuments}",
            'documents.*' => 'nullable|file|max:4096|mimetypes:application/pdf,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            // Repeater
            'trainings' => "bail|required|array|min:1|max:{$maxTrainings}",
            'trainings.*.title' => 'required|string|max:190',
            'trainings.*.institution' => 'required|string|max:190',
            'trainings.*.period_start' => 'required|date',
            'trainings.*.period_end' => 'nullable|date',
            'employments' => "bail|required|array|min:1|max:{$maxEmployments}",
            'employments.*.company' => 'required|string|max:190',
            'employments.*.position_start' => 'required|string|max:190',
            'employments.*.position_end' => 'nullable|string|max:190',
            'employments.*.period_start' => 'required|date',
            'employments.*.period_end' => 'nullable|date',
            'employments.*.reason_for_leaving' => 'nullable|string|max:255',
            'employments.*.job_description' => 'nullable|string',
            'references' => "bail|required|array|min:1|max:1",
            'references.*.name' => 'required|string|max:190',
            'references.*.job_title' => 'required|string|max:190',
            'references.*.company' => 'required|string|max:190',
            'references.*.contact' => 'required|string|max:190',
            // === Gaji & Kesiapan Kerja ===
            'current_salary' => 'nullable|numeric|min:0|max:999999999999.99',
            'expected_salary' => 'nullable|numeric|min:0|max:999999999999.99',
            'expected_facilities' => 'nullable|string',
            'available_start_date' => 'nullable|date|after_or_equal:today',
            'work_motivation' => 'nullable|string',
            // === Kesehatan ===
            'medical_history' => 'nullable|string',
            'last_medical_checkup' => 'nullable|string|max:255'
        ]);


        // ===== VALIDASI MANUAL RANGE TANGGAL DI REPEATER =====
        $errors = [];
        foreach ((array) $request->input('trainings', []) as $i => $t) {
            $start = $t['period_start'] ?? null;
            $end = $t['period_end'] ?? null;
            $startTs = $start ? strtotime((string) $start) : false;
            $endTs = $end ? strtotime((string) $end) : false;
            if ($startTs !== false && $endTs !== false && $endTs < $startTs) {
                $errors["trainings.$i.period_end"] = 'Tanggal selesai pelatihan #' . ($i + 1) . ' harus >= tanggal mulai.';
            }
        }
        foreach ((array) $request->input('employments', []) as $i => $e) {
            $start = $e['period_start'] ?? null;
            $end = $e['period_end'] ?? null;
            $startTs = $start ? strtotime((string) $start) : false;
            $endTs = $end ? strtotime((string) $end) : false;
            if ($startTs !== false && $endTs !== false && $endTs < $startTs) {
                $errors["employments.$i.period_end"] = 'Tanggal selesai pekerjaan #' . ($i + 1) . ' harus >= tanggal mulai.';
            }
        }
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        // Helper: enum/string opsional → null
        $nullIfBlank = static fn($v) => (isset($v) && $v !== '') ? $v : null;

        // ===== SIMPAN DALAM TRANSAKSI =====
        DB::transaction(function () use ($user, $validated, $request, $nullIfBlank, $maxDocuments) {
            /** @var CandidateProfile $profile */
            $profile = CandidateProfile::firstOrCreate(['user_id' => $user->id], ['full_name' => $user->name]);

            // Isi field profil
            $profile->fill([
                'poh_id' => $validated['poh_id'],
                'full_name' => $validated['full_name'],
                'nickname' => $request->input('nickname'),
                'gender' => $validated['gender'],
                'age' => (int) $validated['age'],
                'birthplace' => $validated['birthplace'],
                'birthdate' => $validated['birthdate'],
                'nik' => $validated['nik'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'whatsapp' => $request->input('whatsapp'),
                'last_education' => $validated['last_education'],
                'education_major' => $validated['education_major'],
                'education_school' => $validated['education_school'],
                // KTP
                'ktp_address' => $validated['ktp_address'],
                'ktp_rt' => $request->input('ktp_rt'),
                'ktp_rw' => $request->input('ktp_rw'),
                'ktp_village' => $validated['ktp_village'],
                'ktp_district' => $validated['ktp_district'],
                'ktp_city' => $validated['ktp_city'],
                'ktp_province' => $validated['ktp_province'],
                'ktp_postal_code' => $validated['ktp_postal_code'],
                'ktp_residence_status' => $nullIfBlank($request->input('ktp_residence_status')),
                // Domisili
                'domicile_address' => $validated['domicile_address'],
                'domicile_rt' => $request->input('domicile_rt'),
                'domicile_rw' => $request->input('domicile_rw'),
                'domicile_village' => $validated['domicile_village'],
                'domicile_district' => $validated['domicile_district'],
                'domicile_city' => $validated['domicile_city'],
                'domicile_province' => $validated['domicile_province'],
                'domicile_postal_code' => $validated['domicile_postal_code'],
                'domicile_residence_status' => $nullIfBlank($request->input('domicile_residence_status')),
                // Pernyataan
                'motivation' => $request->input('motivation'),
                'has_relatives' => $request->boolean('has_relatives'),
                'relatives_detail' => $request->input('relatives_detail'),
                'worked_before' => $request->boolean('worked_before'),
                'worked_before_position' => $request->input('worked_before_position'),
                'worked_before_duration' => $request->input('worked_before_duration'),
                'applied_before' => $request->boolean('applied_before'),
                'applied_before_position' => $request->input('applied_before_position'),
                'willing_out_of_town' => $request->boolean('willing_out_of_town'),
                'not_willing_reason' => $request->input('not_willing_reason'),
                'current_salary' => $validated['current_salary'] ?? null,
                'expected_salary' => $validated['expected_salary'] ?? null,
                'expected_facilities' => $validated['expected_facilities'] ?? null,
                'available_start_date' => $validated['available_start_date'] ?? null,
                'work_motivation' => $validated['work_motivation'] ?? null,
                'medical_history' => $validated['medical_history'] ?? null,
                'last_medical_checkup' => $validated['last_medical_checkup'] ?? null,
            ]);

            // Handle SMA/SMK and other education extras
            $extras = $profile->extras ?? [];
            if ($validated['last_education'] === 'SMA_SMK') {
                $extras['sma_smk_type'] = $request->input('sma_smk_type', 'SMA');
                $extras['sma_smk_school'] = $request->input('sma_smk_school', '');
            } else {
                unset($extras['sma_smk_type'], $extras['sma_smk_school']);
            }
            if ($validated['last_education'] === 'LAINNYA') {
                $extras['other_education'] = $request->input('other_education', '');
            } else {
                unset($extras['other_education']);
            }
            $profile->extras = $extras;

            // Upload aman
            $userFolder = 'candidates/u_' . $user->id;

            if ($request->hasFile('cv')) {
                $cv = $request->file('cv');
                $safeName = $this->safeOriginalName($cv->getClientOriginalName());
                $path = $cv->storeAs($userFolder . '/cv', Str::uuid() . '_' . $safeName, 'public');
                $profile->cv_path = $path;
            }

            $docs = is_array($profile->documents ?? []) ? $profile->documents : [];
            if ($request->hasFile('documents')) {
                $incomingDocs = array_values(array_filter((array) $request->file('documents')));
                if (count($docs) + count($incomingDocs) > $maxDocuments) {
                    throw ValidationException::withMessages([
                        'documents' => "Maksimal total dokumen adalah {$maxDocuments} file.",
                    ]);
                }
                foreach ($incomingDocs as $f) {
                    if (!$f)
                        continue;
                    $safeName = $this->safeOriginalName($f->getClientOriginalName());
                    $p = $f->storeAs($userFolder . '/docs', Str::uuid() . '_' . $safeName, 'public');
                    $docs[] = ['name' => $safeName, 'path' => $p];
                }
                $profile->documents = $docs;
            }

            $profile->save();

            // ===== Repeater: hapus lama, insert baru (bulk) =====
            $now = now();

            // TRAININGS (PK UUID → isi id manual)
            $profile->trainings()->delete();
            $trainRows = [];
            foreach ((array) $request->input('trainings', []) as $i => $row) {
                if (!filled($row['title'] ?? null))
                    continue;
                $trainRows[] = [
                    'id' => (string) Str::uuid(),
                    'candidate_profile_id' => $profile->id,
                    'order_no' => $i,
                    'title' => Str::limit((string) $row['title'], 190, ''),
                    'institution' => $row['institution'] ?? null,
                    'period_start' => $row['period_start'] ?? null,
                    'period_end' => $row['period_end'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($trainRows) {
                DB::table('candidate_trainings')->insert($trainRows);
            }

            // EMPLOYMENTS (PK UUID → isi id manual)
            $profile->employments()->delete();
            $empRows = [];
            foreach ((array) $request->input('employments', []) as $i => $row) {
                if (!filled($row['company'] ?? null))
                    continue;
                $empRows[] = [
                    'id' => (string) Str::uuid(),
                    'candidate_profile_id' => $profile->id,
                    'order_no' => $i,
                    'company' => Str::limit((string) $row['company'], 190, ''),
                    'position_start' => $row['position_start'] ?? null,
                    'position_end' => $row['position_end'] ?? null,
                    'period_start' => $row['period_start'] ?? null,
                    'period_end' => $row['period_end'] ?? null,
                    'reason_for_leaving' => Arr::get($row, 'reason_for_leaving'),
                    'job_description' => Arr::get($row, 'job_description'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($empRows) {
                DB::table('candidate_employments')->insert($empRows);
            }

            // REFERENCES
            $profile->references()->delete();
            $refRows = [];
            foreach ((array) $request->input('references', []) as $i => $row) {
                if (!filled($row['name'] ?? null))
                    continue;
                $refRows[] = [
                    'id' => (string) Str::uuid(),  // <-- aktifkan jika tabel references PK UUID
                    'candidate_profile_id' => $profile->id,
                    'order_no' => $i,
                    'name' => Str::limit((string) $row['name'], 190, ''),
                    'job_title' => Arr::get($row, 'job_title'),
                    'company' => Arr::get($row, 'company'),
                    'contact' => Arr::get($row, 'contact'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($refRows) {
                DB::table('candidate_references')->insert($refRows);
            }
        });

        // Redirect
        return redirect()->route('jobs.show', $job)->with('success', 'Data kandidat berhasil disimpan.');
    }

    /**
     * ADMIN: daftar kandidat (pencarian ringan & cepat).
     */
    public function adminIndex(Request $request)
    {
        $qRaw = (string) $request->query('q', '');
        $q = Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '', 120, '');
        $like = $q !== '' ? '%' . addcslashes($q, '\%_') . '%' : null;

        // Ambil daftar posisi (job) untuk filter
        $jobs = \App\Models\Job::orderBy('title')->pluck('title', 'id');

        $jobId = $request->query('job_id');

        $profiles = CandidateProfile::query()
            ->select(['candidate_profiles.id', 'candidate_profiles.user_id', 'candidate_profiles.full_name', 'candidate_profiles.email', 'candidate_profiles.phone', 'candidate_profiles.nik', 'candidate_profiles.updated_at'])
            ->withCount(['trainings', 'employments', 'references'])
            ->with(['user.jobApplications' => function ($q) use ($jobId) {
                if ($jobId) $q->where('job_id', $jobId);
                $q->with('job:id,title');
            }])
            ->when($like !== null, function ($w) use ($like) {
                $w->where(function ($s) use ($like) {
                    $s->where('full_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('nik', 'like', $like);
                });
            })
            ->when($jobId, function ($q) use ($jobId) {
                $q->whereHas('user.jobApplications', function ($a) use ($jobId) {
                    $a->where('job_id', $jobId);
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.candidates.index', compact('profiles', 'q', 'jobs', 'jobId'));
    }

    /**
     * ADMIN: detail kandidat.
     */
    public function adminShow(CandidateProfile $profile)
    {
        $profile->load([
            'trainings' => fn($q) => $q->orderBy('order_no')->select(['id', 'candidate_profile_id', 'order_no', 'title', 'institution', 'period_start', 'period_end']),
            'employments' => fn($q) => $q->orderBy('order_no')->select(['id', 'candidate_profile_id', 'order_no', 'company', 'position_start', 'position_end', 'period_start', 'period_end', 'reason_for_leaving', 'job_description']),
            'references' => fn($q) => $q->orderBy('order_no')->select(['id', 'candidate_profile_id', 'order_no', 'name', 'job_title', 'company', 'contact']),
            'user:id,name,email',
        ]);

        return view('admin.candidates.show', compact('profile'));
    }

    /**
     * ADMIN: lihat / unduh CV kandidat (aman).
     */
    public function adminCv(CandidateProfile $profile)
    {
        if (!$profile->cv_path) {
            abort(404, 'CV tidak tersedia.');
        }

        abort_unless($this->isSafeRelativePath((string) $profile->cv_path), 404, 'Path CV tidak valid.');

        // Disk 'public' → cepat untuk file statik
        if (Storage::disk('public')->exists($profile->cv_path)) {
            return response()->file(Storage::disk('public')->path($profile->cv_path));
        }

        // Fallback: stream dari local/private disk
        if (Storage::exists($profile->cv_path)) {
            $path = Storage::path($profile->cv_path);
            return response()->file($path, [
                'Cache-Control' => 'private, max-age=0, no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        abort(404, 'File CV tidak ditemukan.');
    }

    /**
     * Helper: parse string currency format titik ke float
     */
    private function parseCurrency($value)
    {
        if (is_null($value) || $value === '') return null;
        if (is_numeric($value)) return $value;
        $value = str_replace('.', '', $value);
        return is_numeric($value) ? (float) $value : null;
    }

    private function isSafeRelativePath(string $path): bool
    {
        if ($path === '' || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return false;
        }
        return !str_contains($path, '..');
    }

    /**
     * Normalisasi nama file agar aman.
     */
    protected function safeOriginalName(string $name): string
    {
        // Hilangkan path traversal & karakter aneh
        $name = Str::of($name)->replace(['\\', '/'], '')->toString();

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);

        $base = preg_replace('/[^A-Za-z0-9\-\_\.\s]+/u', '', $base) ?: 'file';
        $base = trim(preg_replace('/\s+/', ' ', $base));
        $ext = preg_replace('/[^A-Za-z0-9]+/u', '', $ext);

        return $ext ? ($base . '.' . strtolower($ext)) : $base;
    }
}
