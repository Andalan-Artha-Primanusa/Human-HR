<?php

namespace App\Http\Middleware;

use App\Models\Job;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCandidateProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'pelamar') {
            return $next($request);
        }

        $profile = $user->candidateProfile()
            ->withCount(['trainings', 'employments', 'references'])
            ->first();

        $missing = $profile
            ? $profile->missingRequiredForApplication()
            : ['Profil kandidat'];

        if ($missing === []) {
            return $next($request);
        }

        $job = $this->resolveJob($request);

        if (! $job) {
            return $next($request);
        }

        return redirect()
            ->route('candidate.profiles.edit', ['job' => $job->id])
            ->withErrors([
                'profile_incomplete' => 'Data profil belum lengkap. Lengkapi semua field wajib sebelum lanjut proses lamaran.',
            ])
            ->with('missing_profile_fields', $missing)
            ->with('info', 'Lengkapi profil kamu dulu sebelum melanjutkan proses lamaran.');
    }

    private function resolveJob(Request $request): ?Job
    {
        $application = $request->route('application');
        if ($application?->job) {
            return $application->job;
        }

        $attempt = $request->route('attempt');
        if ($attempt?->application?->job) {
            return $attempt->application->job;
        }

        $interview = $request->route('interview');
        if ($interview?->application?->job) {
            return $interview->application->job;
        }

        return $request->user()
            ?->jobApplications()
            ->with('job')
            ->latest()
            ->first()
            ?->job;
    }
}
