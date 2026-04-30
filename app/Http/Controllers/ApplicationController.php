<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Offer;
use App\Models\JobApplication;
use App\Models\McuTemplate;
use App\Models\ApplicationStage;
use App\Models\ApplicationFeedback;
use App\Models\PsychotestAttempt;
use App\Models\PsychotestTest;
use App\Models\CandidateProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use App\Models\Poh;
use App\Models\Site;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OfferLetterMail;
use App\Mail\McuMail;

class ApplicationController extends Controller
{
    /* ================================================================
     * STAGE DEFINITIONS
     * ================================================================ */

    protected array $STAGES = [
        'applied',
        'screening',
        'psychotest',
        'hr_iv',
        'user_trainer_iv',
        'offer',
        'mcu',
        'mobilisasi',
        'ground_test',
        'hired',
        'not_qualified',
    ];

    protected array $ALIASES_IN = [
        'apply'              => 'applied',
        'applied'            => 'applied',
        'screening'          => 'screening',
        'screening_cv'       => 'screening',
        'screening-berkas'   => 'screening',
        'screening_berkas'   => 'screening',
        'seleksi_berkas'     => 'screening',
        'psychotest'         => 'psychotest',
        'psikotest'          => 'psychotest',
        'hr_iv'              => 'hr_iv',
        'hriv'               => 'hr_iv',
        'hr-interview'       => 'hr_iv',
        'user_iv'            => 'user_iv',
        'useriv'             => 'user_iv',
        'user-interview'     => 'user_iv',
        'user_trainer_iv'    => 'user_trainer_iv',
        'user-trainer'       => 'user_trainer_iv',
        'trainer_iv'         => 'user_trainer_iv',
        'trainer-interview'  => 'user_trainer_iv',
        'offering'           => 'offer',
        'offer'              => 'offer',
        'ol'                 => 'offer',
        'mcu'                => 'mcu',
        'medical_checkup'    => 'mcu',
        'mobilisasi'         => 'mobilisasi',
        'mobilization'       => 'mobilisasi',
        'ground_test'        => 'ground_test',
        'ground-test'        => 'ground_test',
        'diterima'           => 'hired',
        'hired'              => 'hired',
        'rejected'           => 'not_qualified',
        'not_qualified'      => 'not_qualified',
        'not-qualified'      => 'not_qualified',
        'notqualified'       => 'not_qualified',
        'final'              => 'user_iv',
        'final_interview'    => 'user_iv',
    ];

    protected array $PRETTY = [
        'applied'         => 'Applied',
        'screening'       => 'Screening CV/Berkas Lamaran',
        'psychotest'      => 'Psikotest',
        'hr_iv'           => 'HR Interview',
        'user_trainer_iv' => 'User & Trainer Interview',
        'offer'           => 'OL',
        'mcu'             => 'MCU',
        'mobilisasi'      => 'Mobilisasi',
        'ground_test'     => 'Ground Test',
        'hired'           => 'Hired',
        'not_qualified'   => 'Tidak Lolos',
    ];

    /**
     * Stage-stage yang boleh dipindah SECARA BEBAS oleh admin/hr/superadmin
     * (yaitu semua stage setelah user_trainer_iv)
     */
    protected array $FREE_MOVE_STAGES = [
        'offer',
        'mcu',
        'mobilisasi',
        'ground_test',
        'hired',
        'not_qualified',
    ];

    /** Roles yang boleh melakukan free move */
    protected array $FREE_MOVE_ROLES = ['admin', 'hr', 'superadmin'];

    protected ?Offer $offerJustCreated = null;


    /* ================================================================
     * PUBLIC ENDPOINTS
     * ================================================================ */

    /** Pelamar: daftar lamaran saya */
    public function index(Request $request)
    {
        $this->authorize('viewAny', JobApplication::class);
        $apps = JobApplication::with([
            'job:id,title,division,site_id',
            'job.site:id,code,name',
            'stages.actor:id,name',
            'stages.user:id,name',
            'interviews',
        ])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12);

        return view('applications.mine', compact('apps'));
    }

    /** Pelamar: apply job -> redirect ke wizard profile */
    public function store(Request $request, Job $job)
    {
        abort_if($job->status !== 'open', 403, 'Job is not open');

        $already = JobApplication::where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($already) {
            return redirect()
                ->route('candidate.profiles.edit', ['job' => $job->id])
                ->with('info', 'Kamu sudah melamar. Silakan lengkapi/cek data profil.');
        }

        $data = $request->validate([
            'poh_id' => ['nullable', 'uuid', 'exists:pohs,id'],
        ]);

        DB::transaction(function () use ($request, $job, $data) {
            $app = JobApplication::create([
                'job_id'         => $job->id,
                'user_id'        => $request->user()->id,
                'poh_id'         => $data['poh_id'] ?? null,
                'current_stage'  => 'applied',
                'overall_status' => 'active',
            ]);

            ApplicationStage::create([
                'application_id' => $app->id,
                'stage_key'      => 'applied',
                'status'         => 'pending',
                'score'          => null,
                'payload'        => ['note' => 'Initial application submitted'],
                'acted_by'       => $request->user()->id,
                'user_id'        => $request->user()->id,
                'notes'          => null,
            ]);

            CandidateProfile::firstOrCreate(
                ['user_id' => $request->user()->id],
                ['full_name' => $request->user()->name]
            );
        });

        return redirect()
            ->route('candidate.profiles.edit', ['job' => $job->id])
            ->with('success', 'Lamaran dibuat. Lengkapi data profil & riwayat kamu ya.');
    }

    /** Pelamar/Admin: detail lamaran */
    public function show(JobApplication $application)
    {
        $this->authorize('view', $application);

        $application->load([
            'job:id,title,division,site_id',
            'job.site:id,code,name',
            'stages.actor:id,name',
            'stages.user:id,name',
            'feedbacks.user:id,name',
            'interviews',
            'offer',
        ]);

        return view('applications.show', compact('application'));
    }

    /** Admin: daftar semua aplikasi + filter */
    public function adminIndex(Request $request)
    {
        $this->authorize('viewAdmin', JobApplication::class);
        $filters = $request->validate([
            'q'     => ['nullable', 'string', 'max:120'],
            'stage' => ['nullable', 'string', 'max:50'],
            'site'  => ['nullable', 'string', 'max:50'],
        ]);

        $q    = Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string) ($filters['q'] ?? ''))) ?? '', 120, '');
        $like = $q !== '' ? '%' . addcslashes($q, '\\%_') . '%' : null;
        $stage = $this->normalizeStage($filters['stage'] ?? '');
        $site  = (string) ($filters['site'] ?? '');

        $apps = JobApplication::query()
            ->with([
                'job:id,title,division,site_id',
                'job.site:id,code,name',
                'user:id,name',
                'stages.actor:id,name',
                'stages.user:id,name',
            ])
            ->when($like !== null, function ($q) use ($like) {
                $q->where(function ($w) use ($like) {
                    $w->whereHas('user', fn($u) => $u->where('name', 'like', $like))
                      ->orWhereHas('job', fn($j) => $j->where('title', 'like', $like)
                          ->orWhere('division', 'like', $like)
                          ->orWhereHas('site', fn($s) => $s->where('code', 'like', $like)));
                });
            })
            ->when($stage, fn($q) => $q->where('current_stage', $stage))
            ->when($site,  fn($q) => $q->whereHas('job.site', fn($s) => $s->where('code', $site)))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.applications.index', compact('apps'));
    }

    /** Kanban board */
    public function board(Request $request)
    {
        $this->authorize('viewAdmin', JobApplication::class);
        $user   = auth()->user();
        $stages = $this->STAGES;

        $query = JobApplication::with([
            'job:id,title,division,site_id',
            'job.site:id,code,name',
            'user:id,name,role',
            'stages.actor:id,name',
            'stages.user:id,name',
            'feedbacks',
            'interviews',
            'offer',
        ]);

        if (!in_array($user->role, ['admin', 'hr', 'superadmin'])) {
            $query->where('user_id', $user->id);
        }

        $apps = $query
            ->when($request->filled('job_id'), fn($q) => $q->where('job_id', $request->job_id))
            ->when($request->filled('only'), function ($q) use ($request) {
                $only = collect(explode(',', $request->only))
                    ->map(fn($s) => $this->normalizeStage($s))
                    ->filter()
                    ->values()
                    ->all();
                return $q->whereIn('current_stage', $only);
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%' . addcslashes(trim($request->q), '\\%_') . '%';
                return $q->where(function ($w) use ($like) {
                    $w->whereHas('user', fn($u) => $u->where('name', 'like', $like))
                      ->orWhereHas('job', fn($j) => $j->where('title', 'like', $like));
                });
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $grouped = collect($stages)->mapWithKeys(fn($s) => [$s => collect()]);
        foreach ($apps as $a) {
            $key = $this->normalizeStage($a->current_stage) ?: 'applied';
            if ($key === 'user_iv') $key = 'user_trainer_iv';
            if (!in_array($key, $stages, true)) $key = 'applied';
            $grouped[$key]->push($a);
        }

        $pohs = Poh::all(['id', 'name']);
        $sites = Site::all(['id', 'code', 'name']);
        $mcuTemplate = McuTemplate::where('is_active', true)->first() ?: McuTemplate::first();

        return view('admin.applications.board', compact('stages', 'grouped', 'pohs', 'sites', 'mcuTemplate'));
    }

    /**
     * ================================================================
     * FEEDBACK STORE (AJAX)
     * Endpoint: POST /admin/applications/feedback
     * Dipakai oleh modal "Isi Feedback" di kanban
     * ================================================================
     */
    public function storeFeedback(Request $request)
    {
        $validated = $request->validate([
            'application_id' => ['required', 'uuid', 'exists:job_applications,id'],
            'stage_key'      => ['required', 'string'],
            'role'           => ['required', 'in:hr,karyawan,trainer,pelamar'],
            'feedback'       => ['required', 'string', 'max:2000'],
            'approve'        => ['required', 'in:yes,no'],
        ]);

        $application = JobApplication::findOrFail($validated['application_id']);
        $this->authorize('giveFeedback', $application);

        /** @var \App\Models\User $actor */
        $actor = auth()->user();

        // Validasi: hanya HR/admin/superadmin boleh simpan feedback HR
        if ($validated['role'] === 'hr' && !in_array($actor->role, ['admin', 'hr', 'superadmin'])) {
            abort(403, 'Hanya HR, Admin, atau Superadmin yang dapat mengisi feedback HR.');
        }

        $feedback = DB::transaction(function () use ($validated, $actor) {
            $fb = ApplicationFeedback::create([
                'application_id' => $validated['application_id'],
                'stage_key'      => $validated['stage_key'],
                'role'           => $validated['role'],
                'feedback'       => $validated['feedback'],
                'approve'        => $validated['approve'],
                'user_id'        => $actor->id,
            ]);

            // Mirror ke kolom langsung di job_applications untuk kompatibilitas backward
            /** @var JobApplication $app */
            $app = JobApplication::find($validated['application_id']);
            if ($app) {
                if ($validated['role'] === 'hr') {
                    $app->feedback_hr  = $validated['feedback'];
                    $app->approve_hr   = $validated['approve'];
                    $app->save();
                } elseif ($validated['role'] === 'pelamar') {
                    $app->feedback_employee  = $validated['feedback'];
                    $app->approve_employee   = $validated['approve'];
                    $app->save();
                } elseif ($validated['role'] === 'karyawan') {
                    $app->feedback_user  = $validated['feedback'];
                    $app->approve_user   = $validated['approve'];
                    $app->save();
                } elseif ($validated['role'] === 'trainer') {
                    $app->feedback_trainer  = $validated['feedback'];
                    $app->approve_trainer   = $validated['approve'];
                    $app->save();
                }
            }

            return $fb;
        });

        return response()->json([
            'ok'       => true,
            'feedback' => $feedback->only(['id', 'role', 'feedback', 'approve']),
        ]);
    }

    public function deleteFeedback(Request $request)
    {
        $validated = $request->validate([
            'application_id' => ['required', 'uuid', 'exists:job_applications,id'],
            'role'           => ['required', 'in:hr,karyawan,trainer,pelamar'],
        ]);

        $actor = auth()->user();
        if ($validated['role'] === 'hr' && !in_array($actor->role, ['admin', 'hr', 'superadmin'])) {
            abort(403, 'Hanya HR, Admin, atau Superadmin yang dapat menghapus feedback HR.');
        }

        DB::transaction(function () use ($validated) {
            ApplicationFeedback::where('application_id', $validated['application_id'])
                ->where('role', $validated['role'])
                ->delete();

            $app = JobApplication::find($validated['application_id']);
            if ($app) {
                if ($validated['role'] === 'hr') {
                    $app->feedback_hr  = null;
                    $app->approve_hr   = null;
                } elseif ($validated['role'] === 'pelamar') {
                    $app->feedback_employee  = null;
                    $app->approve_employee   = null;
                } elseif ($validated['role'] === 'karyawan') {
                    $app->feedback_user  = null;
                    $app->approve_user   = null;
                } elseif ($validated['role'] === 'trainer') {
                    $app->feedback_trainer  = null;
                    $app->approve_trainer   = null;
                }
                $app->save();
            }
        });

        return response()->json(['ok' => true]);
    }

    /**
     * ================================================================
     * MOVE STAGE via POST form (tombol/form)
     * ================================================================
     */
    public function moveStage(Request $request, JobApplication $application)
    {
        $this->authorize('update', $application);
        [$to, $status, $note, $score] = $this->validateMove($request, $application);
        $attempt = $this->applyTransition($application, $to, $status, $note, $score);
        return $this->redirectAfterMove($request, $application, $to, $attempt);
    }

    /**
     * ================================================================
     * MOVE STAGE via AJAX (Kanban drag & drop, free move dropdown)
     * Endpoint: POST /admin/applications/board/move?id={uuid}
     * ================================================================
     */
    public function moveStageAjax(Request $request)
    {
        $request->validate([
            'id' => ['required', 'uuid', 'exists:job_applications,id'],
        ]);

        /** @var JobApplication $application */
        $application = JobApplication::with(['job.manpowerRequirement'])->findOrFail($request->input('id'));

        $actor = auth()->user();

        // Cek apakah ini adalah FREE MOVE (setelah user_trainer_iv)
        $toRaw    = $request->input('to') ?? $request->input('to_stage');
        $toStage  = $this->normalizeStage($toRaw) ?: 'applied';
        $fromStage = $application->current_stage;

        $isFreeMoveAllowed = $this->canFreeMoveFrom($fromStage, $actor->role ?? '');

        // Jika bukan free move, validasi normal
        if (!$isFreeMoveAllowed) {
            [$to, $status, $note, $score] = $this->validateMove($request, $application);
        } else {
            // Free move: validasi minimal
            $to     = $toStage;
            $status = $request->input('status', 'pending');
            $note   = $request->input('note');
            $score  = $request->input('score') ? (float) $request->input('score') : null;
        }

        $attempt = $this->applyTransition($application, $to, $status, $note, $score);

        return response()->json([
            'ok'       => true,
            'moved_to' => $to,
            'id'       => $application->id,
            'attempt'  => $attempt?->id ?? null,
        ]);
    }


    /* ================================================================
     * HELPERS
     * ================================================================ */

    /**
     * Apakah perpindahan dari $fromStage termasuk "free move"
     * yang diperbolehkan untuk role tertentu?
     *
     * Free move aktif jika:
     * 1. User adalah admin/hr/superadmin
     * 2. Stage asal SUDAH MELEWATI user_trainer_iv (index > index user_trainer_iv)
     *    ATAU stage asal sendiri adalah user_trainer_iv
     */
    protected function canFreeMoveFrom(string $fromStage, string $role): bool
    {
        if (!in_array($role, $this->FREE_MOVE_ROLES)) {
            return false;
        }

        $trainerIdx = $this->stageIndex('user_trainer_iv');
        $fromIdx    = $this->stageIndex($fromStage);

        // Boleh free move jika dari user_trainer_iv ke atas
        return $fromIdx !== -1 && $fromIdx >= $trainerIdx;
    }

    protected function normalizeStage(?string $s): ?string
    {
        if (!$s) return null;
        $key = strtolower(trim($s));
        return $this->ALIASES_IN[$key] ?? (in_array($key, $this->STAGES, true) ? $key : null);
    }

    protected function stageIndex(string $s): int
    {
        $idx = array_search($s, $this->STAGES, true);
        return $idx === false ? -1 : $idx;
    }

    protected function isBackwardBeforeOffer(string $from, string $to): bool
    {
        $offerIdx = $this->stageIndex('offer');
        $fromIdx  = $this->stageIndex($from);
        $toIdx    = $this->stageIndex($to);
        if ($offerIdx < 0 || $fromIdx < 0 || $toIdx < 0) return false;
        return $toIdx < $offerIdx && $fromIdx >= $offerIdx;
    }

    protected function purgeOffer(JobApplication $application): void
    {
        $offer = $application->offer()->first();
        if (!$offer) return;
        $this->purgeOfferFiles($offer);
        $offer->delete();
    }

    protected function purgeOfferFiles(Offer $offer): void
    {
        $paths = [];
        if (!empty($offer->file_path)) $paths[] = $offer->file_path;
        $meta  = is_array($offer->meta ?? null) ? $offer->meta : [];
        if (!empty($meta['pdf_path'])) $paths[] = $meta['pdf_path'];
        foreach ($paths as $p) {
            if (Storage::disk('public')->exists($p)) {
                Storage::disk('public')->delete($p);
            }
        }
    }

    /**
     * Validasi input perpindahan stage.
     * Untuk FREE MOVE, validasi ini di-skip (lihat moveStageAjax).
     *
     * @return array{string,string,?string,?float}
     */
    protected function validateMove(Request $request, ?JobApplication $application = null): array
    {
        $allowedStages = array_unique(array_merge($this->STAGES, array_values($this->ALIASES_IN)));
        $allowedStatus = ['pending', 'passed', 'failed', 'no-show', 'reschedule'];

        $toRaw = $request->input('to') ?? $request->input('to_stage');

        $validated = $request->validate([
            'to'               => ['nullable', Rule::in($allowedStages)],
            'to_stage'         => ['nullable', Rule::in($allowedStages)],
            'status'           => ['nullable', Rule::in($allowedStatus)],
            'note'             => ['nullable', 'string'],
            'score'            => ['nullable', 'numeric'],
            'feedback_hr'      => ['nullable', 'string'],
            'approve_hr'       => ['nullable', 'in:yes,no'],
            'feedback_trainer' => ['nullable', 'string'],
            'approve_trainer'  => ['nullable', 'in:yes,no'],
            'feedback_user'    => ['nullable', 'string'],
            'approve_user'     => ['nullable', 'in:yes,no'],
        ]);

        $to     = $this->normalizeStage($toRaw) ?: 'applied';
        $status = $validated['status'] ?? 'pending';
        $note   = $validated['note']   ?? null;
        $score  = isset($validated['score']) ? (float) $validated['score'] : null;

        $fromStage = $application?->current_stage
                   ?? $request->input('from_stage')
                   ?? $request->input('from');

        // Wajib feedback HR saat pindah dari hr_iv
        if (
            $fromStage === 'hr_iv'
            && $to !== 'hr_iv'
            && in_array(auth()->user()?->role, ['admin', 'hr', 'superadmin'])
        ) {
            $feedback = $validated['feedback_hr'] ?? $application->feedback_hr;
            $approve = $validated['approve_hr'] ?? $application->approve_hr;
            if (empty($feedback) || ($approve === null)) {
                abort(422, 'HR wajib mengisi feedback dan setuju/tidak setuju sebelum lanjut dari HR Interview.');
            }
        }

        // Wajib feedback trainer (jika role trainer)
        if (
            in_array($to, ['user_trainer_iv'], true)
            && auth()->user()?->role === 'trainer'
        ) {
            if (empty($validated['feedback_trainer']) || ($validated['approve_trainer'] ?? null) === null) {
                abort(422, 'Trainer wajib mengisi feedback dan setuju/tidak setuju sebelum lanjut.');
            }
        }

        // Wajib feedback karyawan (jika role karyawan, pindah dari user_iv ke user_trainer_iv)
        if (
            $fromStage === 'user_iv'
            && $to === 'user_trainer_iv'
            && auth()->user()?->role === 'karyawan'
        ) {
            if (empty($validated['feedback_user']) || ($validated['approve_user'] ?? null) === null) {
                abort(422, 'User wajib mengisi feedback dan setuju/tidak setuju sebelum lanjut.');
            }
        }

        return [$to, $status, $note, $score];
    }

    /**
     * Terapkan perpindahan stage (atomic transaction).
     * Return: PsychotestAttempt jika dibuat, selain itu null.
     */
    protected function applyTransition(
        JobApplication $application,
        string $to,
        string $status = 'pending',
        ?string $note = null,
        ?float $score = null
    ) {
        $attempt  = null;
        $userId   = Auth::id();
        $actorUser = Auth::user();
        $actor    = $actorUser?->name ?? 'System';

        DB::transaction(function () use ($application, $to, $status, $note, $score, &$attempt, $userId, $actor, $actorUser) {
            $from        = $application->current_stage;
            $prevOverall = $application->overall_status;

            // Jika mundur sebelum 'offer', hapus Offer + file
            if ($this->isBackwardBeforeOffer($from, $to)) {
                $this->purgeOffer($application);
                $this->offerJustCreated = null;
            }

            // Timeline entry
            ApplicationStage::create([
                'application_id' => $application->id,
                'stage_key'      => $to,
                'status'         => $status ?: 'pending',
                'score'          => $score,
                'payload'        => ['note' => $note],
                'acted_by'       => $userId,
                'user_id'        => $userId,
                'notes'          => $note,
            ]);

            // Simpan feedback HR jika dari hr_iv
            if (
                $from === 'hr_iv'
                && $to !== 'hr_iv'
                && in_array($actorUser?->role, ['admin', 'hr', 'superadmin'])
                && (request('feedback_hr') !== null || request('approve_hr') !== null)
            ) {
                $application->feedback_hr = request('feedback_hr');
                $application->approve_hr  = request('approve_hr');
                $application->save();

                ApplicationFeedback::firstOrCreate(
                    [
                        'application_id' => $application->id,
                        'stage_key'      => 'hr_iv',
                        'role'           => 'hr',
                        'user_id'        => $userId,
                    ],
                    [
                        'feedback' => request('feedback_hr'),
                        'approve'  => request('approve_hr'),
                    ]
                );
            }

            // Simpan feedback Pelamar (Employee)
            if (
                $actorUser?->role === 'pelamar'
                && (request('feedback_user') !== null || request('approve_user') !== null)
            ) {
                $application->feedback_employee = request('feedback_user');
                $application->approve_employee  = request('approve_user');
                $application->save();

                ApplicationFeedback::firstOrCreate(
                    [
                        'application_id' => $application->id,
                        'stage_key'      => $from,
                        'role'           => 'pelamar',
                        'user_id'        => $userId,
                    ],
                    [
                        'feedback' => request('feedback_user'),
                        'approve'  => request('approve_user'),
                    ]
                );
            }

            // Simpan feedback Karyawan (User)
            if (
                $actorUser?->role === 'karyawan'
                && (request('feedback_user') !== null || request('approve_user') !== null)
            ) {
                $application->feedback_user = request('feedback_user');
                $application->approve_user  = request('approve_user');
                $application->save();

                ApplicationFeedback::firstOrCreate(
                    [
                        'application_id' => $application->id,
                        'stage_key'      => $from,
                        'role'           => 'karyawan',
                        'user_id'        => $userId,
                    ],
                    [
                        'feedback' => request('feedback_user'),
                        'approve'  => request('approve_user'),
                    ]
                );
            }

            // Simpan feedback Trainer
            if (
                $actorUser?->role === 'trainer'
                && (request('feedback_trainer') !== null || request('approve_trainer') !== null)
            ) {
                $application->feedback_trainer = request('feedback_trainer');
                $application->approve_trainer  = request('approve_trainer');
                $application->save();

                ApplicationFeedback::firstOrCreate(
                    [
                        'application_id' => $application->id,
                        'stage_key'      => $from,
                        'role'           => 'trainer',
                        'user_id'        => $userId,
                    ],
                    [
                        'feedback' => request('feedback_trainer'),
                        'approve'  => request('approve_trainer'),
                    ]
                );
            }

            // Update stage
            $application->update(['current_stage' => $to]);

            // Buat/ambil Offer saat masuk ke 'offer' atau 'hired'
            if (in_array($to, ['offer', 'hired'], true)) {
                $existing = $application->offer()->first();
                if (!$existing) {
                    $job   = $application->job()->with(['site:id,code'])->first();
                    $gross = (float) ($job->default_gross      ?? 0);
                    $allow = (float) ($job->default_allowance  ?? 0);

                    $existing = $application->offer()->create([
                        'status'        => 'draft',
                        'salary'        => ['gross' => $gross, 'allowance' => $allow],
                        'body_template' => null,
                        'meta'          => [
                            'job_title' => $job?->title,
                            'site_code' => $job?->site?->code,
                        ],
                    ]);
                    // Email dikirim secara manual melalui tombol di Kanban
                }
                $this->offerJustCreated = $existing;
            }

            // MCU Notifikasi
            // Email MCU dikirim secara manual melalui tombol di Kanban

            // Overall status & headcount
            if ($to === 'hired') {
                $job = $application->job()->with('manpowerRequirement', 'site:id,code', 'company:id,code')->first();
                if ($job && $job->manpowerRequirement) {
                    $job->manpowerRequirement->increment('filled_headcount');
                    if ($job->manpowerRequirement->filled_headcount >= $job->manpowerRequirement->budget_headcount) {
                        $job->update(['status' => 'closed']);
                    }
                }
                $application->update(['overall_status' => 'hired']);

                // Auto-generate NIK
                $user = User::find($application->user_id);
                if ($user && empty($user->id_employe)) {
                    $nik   = $this->makeNik($job, 1);
                    $tries = 0;
                    while (User::where('id_employe', $nik)->exists() && $tries < 5) {
                        $nik = $this->makeNik($job, 1);
                        $tries++;
                    }
                    $user->forceFill(['id_employe' => $nik])->save();
                }
            } elseif ($to === 'not_qualified') {
                $application->update(['overall_status' => 'not_qualified']);
            } else {
                if ($application->overall_status !== 'active') {
                    $application->update(['overall_status' => 'active']);
                }
            }

            // Auto-buat attempt psikotes
            if ($to === 'psychotest') {
                $test = PsychotestTest::where('is_active', true)->latest('updated_at')->first();
                if (!$test) {
                    $test = PsychotestTest::create([
                        'name'             => 'Tes Dasar (Auto)',
                        'duration_minutes' => 20,
                        'scoring'          => ['pass_ratio' => 0.6],
                        'is_active'        => true,
                    ]);
                }
                $attempt = PsychotestAttempt::where('application_id', $application->id)
                    ->where('test_id', $test->id)
                    ->first();
                if (!$attempt) {
                    $attempt = PsychotestAttempt::create([
                        'application_id' => $application->id,
                        'test_id'        => $test->id,
                        'user_id'        => $application->user_id,
                        'attempt_no'     => 1,
                        'status'         => 'pending',
                        'is_active'      => true,
                        'started_at'     => null,
                        'submitted_at'   => null,
                        'expires_at'     => now()->addDays(3),
                        'score'          => null,
                        'meta'           => ['note' => 'auto-created on stage move'],
                    ]);
                }
            }

            // Notifikasi ke pelamar
            $appReload  = $application->fresh(['job:id,title', 'user:id,name']);
            $jobTitle   = $appReload->job?->title ?? '—';
            $toPretty   = $this->PRETTY[$to]   ?? strtoupper($to);
            $fromPretty = $from ? ($this->PRETTY[$from] ?? strtoupper($from)) : null;

            $title = 'Perubahan Proses Lamaran';
            if ($appReload->overall_status === 'hired' && $prevOverall !== 'hired') {
                $body = "Selamat! Status lamaran kamu untuk posisi \"{$jobTitle}\" berubah menjadi DITERIMA.";
            } elseif ($appReload->overall_status === 'not_qualified' && $prevOverall !== 'not_qualified') {
                $body = "Maaf, lamaran kamu untuk posisi \"{$jobTitle}\" tidak melanjutkan proses.";
            } else {
                $stagePart = $fromPretty ? "{$fromPretty} → {$toPretty}" : $toPretty;
                $body = "Tahap lamaran kamu untuk posisi \"{$jobTitle}\" diperbarui menjadi: {$stagePart}.";
            }

            DatabaseNotification::create([
                'id'             => (string) Str::uuid(),
                'type'           => 'app:application.stage_changed',
                'notifiable_type' => User::class,
                'notifiable_id'  => $appReload->user_id,
                'data'           => [
                    'title'            => $title,
                    'body'             => $body,
                    'job_title'        => $jobTitle,
                    'application_id'   => $appReload->id,
                    'job_id'           => $appReload->job_id,
                    'stage_from'       => $from,
                    'stage_to'         => $to,
                    'stage_from_label' => $fromPretty,
                    'stage_to_label'   => $toPretty,
                    'overall_status'   => $appReload->overall_status,
                    'status_label'     => strtoupper($appReload->overall_status ?? 'ACTIVE'),
                    'actor_id'         => $userId,
                    'actor_name'       => $actor,
                    'note'             => $note,
                    'score'            => $score,
                    'when_wib'         => Carbon::now('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                    'url'              => route('applications.mine'),
                ],
                'read_at'    => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return $attempt;
    }

    protected function redirectAfterMove(Request $request, JobApplication $application, string $to, $attempt = null)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'moved_to' => $to, 'id' => $application->id]);
        }

        $isOwner = $request->user() && (string) $request->user()->id === (string) $application->user_id;

        switch ($to) {
            case 'psychotest':
                if ($isOwner && $attempt) {
                    return redirect()->route('psychotest.show', $attempt)->with('ok', 'Silakan mulai Psikotes.');
                }
                return redirect()->route('admin.psychotests.index', ['focus' => $application->id])->with('ok', 'Stage dipindah ke PSIKOTEST.');

            case 'hr_iv':
            case 'user_iv':
            case 'user_trainer_iv':
                if (auth()->user() && in_array(auth()->user()->role, ['karyawan', 'trainer'])) {
                    return redirect()->route('kanban.mine')->with('ok', 'Feedback berhasil, stage dipindah ke ' . strtoupper($this->PRETTY[$to] ?? $to) . '.');
                }
                return redirect()->route('admin.interviews.index', ['focus' => $application->id])->with('ok', 'Stage dipindah ke ' . strtoupper($this->PRETTY[$to] ?? $to) . '.');

            case 'offer': {
                $offer = $this->offerJustCreated ?: $application->offer()->first();
                if ($offer) {
                    return redirect()->route('admin.offers.pdf', $offer)->with('ok', 'Stage dipindah ke OL. Menampilkan Offering Letter.');
                }
                return redirect()->route('admin.offers.index', ['focus' => $application->id])->with('ok', 'Stage dipindah ke OL.');
            }

            case 'mcu':
            case 'mobilisasi':
            case 'ground_test':
                return redirect()->route('admin.applications.index', ['focus' => $application->id])->with('ok', 'Stage dipindah ke ' . strtoupper($this->PRETTY[$to] ?? $to) . '.');

            case 'hired': {
                $offer = $this->offerJustCreated ?: $application->offer()->first();
                if ($offer) {
                    return redirect()->route('admin.offers.pdf', $offer)->with('ok', 'DITERIMA. Menampilkan Offering Letter.');
                }
                return redirect()->route('admin.applications.index', ['focus' => $application->id])->with('ok', 'Stage dipindah ke DITERIMA.');
            }

            case 'not_qualified':
                return redirect()->route('admin.applications.index', ['focus' => $application->id])->with('ok', 'Stage dipindah ke TIDAK LOLOS.');

            default:
                return redirect()->back(303)->with('ok', 'Stage dipindah ke: ' . strtoupper($this->PRETTY[$to] ?? $to));
        }
    }


    /* ================================================================
     * NIK GENERATOR
     * ================================================================ */

    protected function makeNik(Job $job, int $algo = 1): string
    {
        $companyRaw = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($job->company?->code ?? '')) ?? '');
        $company3   = str_pad(substr($companyRaw, 0, 3), 3, 'X');

        $siteCodeRaw = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($job->site?->code ?? '')) ?? '');
        $siteMap     = ['HO' => '01', 'BGG' => '02', 'SBS' => '03', 'DBK' => '04', 'POS' => '05', 'IBP' => '06'];
        $site2       = preg_match('/^\d{2}$/', $siteCodeRaw) ? $siteCodeRaw : ($siteMap[$siteCodeRaw] ?? '00');

        $yy = now()->format('y');
        $mm = now()->format('m');

        $maxSeq = 0;
        User::query()
            ->select(['id', 'id_employe'])
            ->whereNotNull('id_employe')
            ->orderBy('id')
            ->chunkById(1000, function ($users) use (&$maxSeq, $yy) {
                foreach ($users as $user) {
                    $nik = $user->id_employe;
                    if (!is_string($nik)) continue;
                    if (preg_match('/^[A-Z0-9]{3}\d{2}(\d{2})\d{2}(\d{5})$/', $nik, $m) !== 1) continue;
                    if (($m[1] ?? '') !== $yy) continue;
                    $seq = (int) ($m[2] ?? 0);
                    if ($seq > $maxSeq) $maxSeq = $seq;
                }
            }, 'id');

        $nextSeq = str_pad((string) ($maxSeq + 1), 5, '0', STR_PAD_LEFT);
        return "{$company3}{$site2}{$yy}{$mm}{$nextSeq}";
    }

    /**
     * Send Offer Email with customizable body and salary.
     */
    public function sendOfferEmail(Request $request, JobApplication $application)
    {
        \Log::info("sendOfferEmail started for app: {$application->id}");
        $this->authorize('sendOffer', $application);

        try {
            $data = $request->validate([
                'gross' => 'required|numeric|min:0',
                'allowance' => 'required|numeric|min:0',
                'email_body' => 'required|string',
                'doc_no'          => 'nullable|string',
                'grade_level'     => 'nullable|string',
                'poh'             => 'nullable|string',
                'lokasi'          => 'nullable|string',
                'contract_status' => 'nullable|string',
                'join_date'       => 'nullable', 
                'working_hours'   => 'nullable|string',
                'working_schedule'=> 'nullable|string',
                'meals_allowance' => 'nullable|string',
                'overtime'        => 'nullable|string',
                'tax_borne_by'    => 'nullable|string',
                'deductions'      => 'nullable|string',
                'signer_name'     => 'nullable|string',
                'signer_title'    => 'nullable|string',
                'company'         => 'nullable|string',
                'footer_code'     => 'nullable|string',
                'footer_version'  => 'nullable|string',
                'footer_page_text'=> 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error("Validation failed for sendOfferEmail: " . json_encode($e->errors()));
            throw $e;
        }

        \Log::info("Validation passed for app: {$application->id}");

        $offer = $application->offer()->first();
        $meta = $offer ? ($offer->meta ?? []) : [];
        $metaFields = [
            'doc_no', 'grade_level', 'poh', 'lokasi', 'contract_status', 
            'join_date', 'working_hours', 'working_schedule', 
            'meals_allowance', 'overtime', 'tax_borne_by', 'deductions',
            'signer_name', 'signer_title', 'company', 'footer_code', 'footer_version',
            'footer_page_text'
        ];
        foreach ($metaFields as $f) {
            if ($request->has($f)) {
                $meta[$f] = $data[$f];
            }
        }

        if ($offer) {
            $offer->update([
                'salary' => ['gross' => (float)$data['gross'], 'allowance' => (float)$data['allowance']],
                'body_template' => $data['email_body'],
                'meta' => $meta,
                'status' => 'sent',
            ]);
        } else {
            $offer = $application->offer()->create([
                'status' => 'sent',
                'salary' => ['gross' => (float)$data['gross'], 'allowance' => (float)$data['allowance']],
                'body_template' => $data['email_body'],
                'meta' => $meta,
            ]);
        }

        if ($application->user && $application->user->email) {
            \Log::info("Target email: " . $application->user->email);
            try {
                $mail = new OfferLetterMail($offer);
                $mail->bodyContent = $data['email_body'];
                Mail::to($application->user->email)->send($mail);
                \Log::info("Mail sent successfully for app: {$application->id}");

                // Tambahkan in-app notification
                \Illuminate\Notifications\DatabaseNotification::create([
                    'id'             => (string) \Illuminate\Support\Str::uuid(),
                    'type'           => 'app:application.offer_sent',
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id'  => $application->user_id,
                    'data'           => [
                        'title'            => 'Offering Letter Diterima',
                        'body'             => 'Kamu menerima email Offering Letter (OL) untuk posisi "' . ($application->job->title ?? '-') . '". Silakan cek kotak masuk atau folder spam di email kamu.',
                        'job_title'        => $application->job->title ?? '-',
                        'application_id'   => $application->id,
                        'job_id'           => $application->job_id,
                        'url'              => route('applications.mine'),
                        'when_wib'         => \Carbon\Carbon::now('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                    ],
                    'read_at'    => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return back()->with('ok', 'Email Offer Letter berhasil dikirim beserta notifikasi.');
            } catch (\Exception $e) {
                \Log::error('Failed to send OfferLetterMail: ' . $e->getMessage());
                return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
            }
        }
        return back()->with('error', 'Kandidat tidak memiliki email valid.');
    }

    /**
     * Send MCU Email with customizable body and document metadata.
     */
    public function sendMcuEmail(Request $request, JobApplication $application)
    {
        \Log::info("sendMcuEmail started for app: {$application->id}");
        $this->authorize('sendMcu', $application);

        try {
            $data = $request->validate([
                'email_body'          => 'required|string',
                'doc_no'              => 'nullable|string',
                'company_name'        => 'nullable|string',
                'city'                => 'nullable|string',
                'project_name'        => 'nullable|string',
                'clinic_name'         => 'required|string',
                'clinic_address'      => 'required|string',
                'clinic_city'         => 'nullable|string',
                'mcu_date'            => 'required|date',
                'mcu_time'            => 'required|string',
                'for_text'            => 'nullable|string',
                'bu_name'             => 'nullable|string',
                'matrix_owner'        => 'nullable|string',
                'package'             => 'nullable|string',
                'notes'               => 'nullable|string',
                'result_emails'       => 'nullable|string',
                'signer_name'         => 'nullable|string',
                'signer_title'        => 'nullable|string',
                'footer_company_name' => 'nullable|string',
                'footer_address'      => 'nullable|string',
                'footer_email'        => 'nullable|string',
                'footer_website'      => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error("Validation failed for sendMcuEmail: " . json_encode($e->errors()));
            throw $e;
        }

        // Save metadata to application
        $application->update([
            'mcu_meta' => $data
        ]);

        if ($application->user && $application->user->email) {
            \Log::info("Target MCU email: " . $application->user->email);
            try {
                $mail = new McuMail($application);
                $mail->bodyContent = $data['email_body'];
                \Mail::to($application->user->email)->send($mail);
                \Log::info("MCU Mail sent successfully for app: {$application->id}");

                // Tambahkan in-app notification
                \Illuminate\Notifications\DatabaseNotification::create([
                    'id'             => (string) \Illuminate\Support\Str::uuid(),
                    'type'           => 'app:application.mcu_sent',
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id'  => $application->user_id,
                    'data'           => [
                        'title'            => 'Undangan MCU',
                        'body'             => 'Kamu menerima email Undangan Medical Check Up (MCU) untuk posisi "' . ($application->job->title ?? '-') . '". Silakan cek kotak masuk atau folder spam di email kamu untuk instruksi lebih lanjut.',
                        'job_title'        => $application->job->title ?? '-',
                        'application_id'   => $application->id,
                        'job_id'           => $application->job_id,
                        'url'              => route('applications.mine'),
                        'when_wib'         => \Carbon\Carbon::now('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                    ],
                    'read_at'    => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return back()->with('ok', 'Undangan MCU berhasil dikirim beserta notifikasi.');
            } catch (\Exception $e) {
                \Log::error('Failed to send McuMail: ' . $e->getMessage());
                return back()->with('error', 'Gagal mengirim email MCU: ' . $e->getMessage());
            }
        }
        return back()->with('error', 'Kandidat tidak memiliki email valid.');
    }
}