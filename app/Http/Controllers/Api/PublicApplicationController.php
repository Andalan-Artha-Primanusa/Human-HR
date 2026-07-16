<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStage;
use App\Models\CandidateProfile;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Support\UploadPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicApplicationController extends Controller
{
    public function uploadCv(Request $request, Job $job): JsonResponse
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
        $path = $file->storeAs(
            UploadPath::forUser($user, 'candidate-profile/cv'),
            Str::uuid() . '_' . $safeName,
            'public'
        );

        $profile->forceFill(['cv_path' => $path])->save();
        $profile->loadCount(['trainings', 'employments', 'references'])->loadMissing('attachments');

        $missing = $profile->missingRequiredForApplication();
        $application = null;

        if (($data['apply'] ?? false) && $missing === []) {
            $application = DB::transaction(function () use ($user, $job) {
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
                        'payload' => ['note' => 'Initial application submitted via API CV upload'],
                        'acted_by' => $user->id,
                        'user_id' => $user->id,
                        'notes' => null,
                    ]);
                }

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
                'user_id' => $user->id,
                'cv_path' => $path,
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
}
