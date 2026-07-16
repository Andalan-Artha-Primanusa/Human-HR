<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStage;
use App\Models\CandidateEmployment;
use App\Models\CandidateProfile;
use App\Models\CandidateReference;
use App\Models\CandidateTraining;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Support\UploadPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicApplicationController extends Controller
{
    public function uploadCv(Request $request, Job $job): JsonResponse
    {
        return $this->storeCvForJob($request, $job);
    }

    public function uploadCvByCode(Request $request): JsonResponse
    {
        $request->validate([
            'code_id' => ['required', 'string', 'max:80'],
        ]);

        $job = Job::where('code', $request->input('code_id'))->first();

        if (! $job) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job dengan code_id tersebut tidak ditemukan.',
            ], 404);
        }

        return $this->storeCvForJob($request, $job);
    }

    public function injectApplicantProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code_id' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'apply' => ['nullable', 'boolean'],
        ]);

        $user = $this->resolveUser($request);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login tidak valid atau email belum diverifikasi.',
            ], 401);
        }

        $job = Job::where('code', $data['code_id'])->first();

        if (! $job) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job dengan code_id tersebut tidak ditemukan.',
            ], 404);
        }

        if ($job->status !== 'open') {
            return response()->json([
                'status' => 'error',
                'message' => 'Job tidak sedang open.',
            ], 403);
        }

        $profile = DB::transaction(function () use ($request, $user) {
            $profile = CandidateProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['full_name' => $user->name, 'email' => $user->email]
            );

            $profileFields = [
                'full_name', 'nickname', 'gender', 'birthplace', 'birthdate', 'age', 'nik', 'phone', 'whatsapp',
                'email', 'source_channel', 'last_education', 'education_major', 'education_school',
                'ktp_address', 'ktp_rt', 'ktp_rw', 'ktp_village', 'ktp_district', 'ktp_city', 'ktp_province',
                'ktp_postal_code', 'ktp_residence_status', 'domicile_address', 'domicile_rt', 'domicile_rw',
                'domicile_village', 'domicile_district', 'domicile_city', 'domicile_province',
                'domicile_postal_code', 'domicile_residence_status', 'motivation', 'has_relatives',
                'relatives_detail', 'worked_before', 'worked_before_position', 'worked_before_duration',
                'applied_before', 'applied_before_position', 'willing_out_of_town', 'not_willing_reason',
                'poh_id', 'current_salary', 'expected_salary', 'expected_facilities', 'available_start_date',
                'work_motivation', 'medical_history', 'last_medical_checkup', 'status_pernikahan',
            ];

            $payload = [];
            foreach ($profileFields as $field) {
                if ($request->has($field)) {
                    $payload[$field] = $request->input($field);
                }
            }

            if ($payload !== []) {
                $profile->forceFill($payload)->save();
            }

            $this->replaceTrainings($profile, $this->arrayPayload($request, 'trainings'));
            $this->replaceEmployments($profile, $this->arrayPayload($request, 'employments'));
            $this->replaceReferences($profile, $this->arrayPayload($request, 'references'));

            return $profile->fresh(['poh', 'trainings', 'employments', 'references', 'attachments'])
                ->loadCount(['trainings', 'employments', 'references']);
        });

        $missing = $profile->missingRequiredForApplication();
        $application = null;

        if (($data['apply'] ?? false) && $missing === []) {
            $application = $this->createApplication($user, $job, 'Initial application submitted via API profile inject');
        }

        return response()->json([
            'status' => 'success',
            'message' => $application
                ? 'Data pelamar berhasil diinject dan lamaran berhasil dibuat.'
                : 'Data pelamar berhasil diinject.',
            'data' => [
                'job_id' => $job->id,
                'code_id' => $job->code,
                'user_id' => $user->id,
                'profile_complete' => $missing === [],
                'missing_required_fields' => $missing,
                'profile' => $profile,
                'application' => $application,
            ],
        ], $application ? 201 : 200);
    }

    private function storeCvForJob(Request $request, Job $job): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login dibutuhkan. Kirim Authorization Bearer token atau email dan password.',
            ], 401);
        }

        if ($job->status !== 'open') {
            return response()->json([
                'status' => 'error',
                'message' => 'Job tidak sedang open.',
            ], 403);
        }

        $data = $request->validate([
            'cv' => ['required', 'file', 'mimes:pdf', 'max:4096'],
            'apply' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email'],
            'password' => ['nullable', 'string'],
        ]);

        $profile = CandidateProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $user->name,
                'email' => $user->email,
            ]
        );

        $file = $request->file('cv');
        $safeName = UploadPath::safeOriginalName($file->getClientOriginalName());
        $storageError = $this->validateUploadDisk();

        if ($storageError) {
            return response()->json([
                'status' => 'error',
                'message' => $storageError,
            ], 500);
        }

        $path = $file->storeAs(
            UploadPath::forUser($user, 'candidate-profile/cv'),
            Str::uuid() . '_' . $safeName,
            'public'
        );

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'CV gagal tersimpan ke storage upload. Cek permission dan path UPLOAD_DIR.',
            ], 500);
        }

        $profile->forceFill(['cv_path' => $path])->save();
        $attachment = $profile->attachments()->updateOrCreate(
            ['label' => 'CV'],
            [
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
            ]
        );
        $profile->loadCount(['trainings', 'employments', 'references'])->loadMissing('attachments');

        $missing = $profile->missingRequiredForApplication();
        $application = null;

        if (($data['apply'] ?? false) && $missing === []) {
            $application = DB::transaction(function () use ($user, $job, $path, $file) {
                $app = $this->createApplication($user, $job, 'Initial application submitted via API CV upload');

                $app->attachments()->updateOrCreate(
                    ['label' => 'CV'],
                    [
                        'path' => $path,
                        'mime' => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]
                );

                return $app->fresh(['job.site', 'job.company', 'stages']);
            });
        }

        return response()->json([
            'status' => 'success',
            'message' => $application
                ? 'CV berhasil diupload dan lamaran berhasil dibuat.'
                : 'CV berhasil diupload ke profil kandidat.',
            'data' => [
                'job_id' => $job->id,
                'code_id' => $job->code,
                'job' => $job->loadMissing('site', 'company'),
                'user_id' => $user->id,
                'cv_path' => $path,
                'attachment' => $attachment,
                'profile_complete' => $missing === [],
                'missing_required_fields' => $missing,
                'application' => $application,
            ],
        ], $application ? 201 : 200);
    }

    private function resolveUser(Request $request): ?User
    {
        if ($request->user()) {
            return $request->user();
        }

        if ($token = $request->bearerToken()) {
            $user = User::where('api_token', hash('sha256', $token))->first();

            if ($user && $user->hasVerifiedEmail()) {
                Auth::setUser($user);
                $request->setUserResolver(fn() => $user);

                return $user;
            }
        }

        $email = mb_strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');

        if ($email === '' || $password === '') {
            return null;
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password) || ! $user->hasVerifiedEmail()) {
            return null;
        }

        Auth::setUser($user);
        $request->setUserResolver(fn() => $user);

        return $user;
    }

    private function createApplication(User $user, Job $job, string $note): JobApplication
    {
        return DB::transaction(function () use ($user, $job, $note) {
            $app = JobApplication::firstOrCreate(
                [
                    'job_id' => $job->id,
                    'user_id' => $user->id,
                ],
                [
                    'current_stage' => 'applied',
                    'overall_status' => 'active',
                ]
            );

            if ($app->wasRecentlyCreated) {
                ApplicationStage::create([
                    'application_id' => $app->id,
                    'stage_key' => 'applied',
                    'status' => 'pending',
                    'score' => null,
                    'payload' => ['note' => $note],
                    'acted_by' => $user->id,
                    'user_id' => $user->id,
                    'notes' => null,
                ]);
            }

            return $app->fresh(['job.site', 'job.company', 'stages', 'attachments']);
        });
    }

    private function arrayPayload(Request $request, string $key): ?array
    {
        if (! $request->has($key)) {
            return null;
        }

        $value = $request->input($key);

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    private function replaceTrainings(CandidateProfile $profile, ?array $rows): void
    {
        if ($rows === null) {
            return;
        }

        $profile->trainings()->delete();
        foreach ($rows as $i => $row) {
            if (! filled($row['title'] ?? null)) {
                continue;
            }

            CandidateTraining::create([
                'candidate_profile_id' => $profile->id,
                'order_no' => $i,
                'title' => $row['title'],
                'institution' => $row['institution'] ?? null,
                'period_start' => $row['period_start'] ?? null,
                'period_end' => $row['period_end'] ?? null,
                'certificate_path' => $row['certificate_path'] ?? null,
                'certificate_name' => $row['certificate_name'] ?? null,
                'cert_valid_from' => $row['cert_valid_from'] ?? null,
                'cert_valid_to' => $row['cert_valid_to'] ?? null,
                'cert_no_expiry' => (bool) ($row['cert_no_expiry'] ?? false),
            ]);
        }
    }

    private function replaceEmployments(CandidateProfile $profile, ?array $rows): void
    {
        if ($rows === null) {
            return;
        }

        $profile->employments()->delete();
        foreach ($rows as $i => $row) {
            if (! filled($row['company'] ?? null)) {
                continue;
            }

            CandidateEmployment::create([
                'candidate_profile_id' => $profile->id,
                'order_no' => $i,
                'company' => $row['company'],
                'position_start' => $row['position_start'] ?? null,
                'position_end' => $row['position_end'] ?? null,
                'period_start' => $row['period_start'] ?? null,
                'period_end' => $row['period_end'] ?? null,
                'reason_for_leaving' => $row['reason_for_leaving'] ?? null,
                'job_description' => $row['job_description'] ?? null,
            ]);
        }
    }

    private function replaceReferences(CandidateProfile $profile, ?array $rows): void
    {
        if ($rows === null) {
            return;
        }

        $profile->references()->delete();
        foreach ($rows as $i => $row) {
            if (! filled($row['name'] ?? null)) {
                continue;
            }

            CandidateReference::create([
                'candidate_profile_id' => $profile->id,
                'order_no' => $i,
                'name' => $row['name'],
                'job_title' => $row['job_title'] ?? null,
                'company' => $row['company'] ?? null,
                'contact' => $row['contact'] ?? null,
            ]);
        }
    }

    private function validateUploadDisk(): ?string
    {
        $root = (string) config('filesystems.disks.public.root');

        if (DIRECTORY_SEPARATOR !== '\\' && str_starts_with($root, '\\\\')) {
            return 'UPLOAD_DIR memakai path UNC Windows, sedangkan server berjalan di Linux. Mount NAS ke path Linux seperti /mnt/hr-karir/uploads lalu set UPLOAD_DIR ke path mount tersebut.';
        }

        if (! is_dir($root) && ! @mkdir($root, 0775, true) && ! is_dir($root)) {
            return 'Folder UPLOAD_DIR tidak bisa dibuat: ' . $root;
        }

        if (! is_writable($root)) {
            return 'Folder UPLOAD_DIR tidak bisa ditulis oleh web server: ' . $root;
        }

        return null;
    }
}
