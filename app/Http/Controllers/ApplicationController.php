<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\ApplicationStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    /**
     * Pelamar: daftar lamaran saya
     */
    public function index(Request $request)
    {
        $apps = JobApplication::with([
                'job:id,title,division,site_id',
                'job.site:id,code,name',
                'stages',
            ])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('applications.mine', compact('apps'));
    }

    /**
     * Pelamar: apply job
     */
    public function store(Request $request, Job $job)
    {
        abort_if($job->status !== 'open', 403, 'Job is not open');

        $exists = JobApplication::where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($exists) {
            return back()->with('warn', 'Kamu sudah melamar posisi ini.');
        }

        DB::transaction(function () use ($request, $job) {
            $app = JobApplication::create([
                'job_id'         => $job->id,
                'user_id'        => $request->user()->id,
                'current_stage'  => 'apply',   // konsisten dgn Blade
                'overall_status' => 'active',
            ]);

            ApplicationStage::create([
                'application_id' => $app->id,
                'stage_key'      => 'apply',
                'status'         => 'pending',
                'payload'        => ['note' => 'Initial application submitted'],
            ]);
        });

        return redirect()->route('applications.mine')->with('ok', 'Lamaran terkirim.');
    }

    /**
     * Admin: daftar semua aplikasi + filter
     * Route: GET admin/applications  -> name: admin.applications.index
     */
    public function adminIndex(Request $request)
    {
        $q     = (string) $request->query('q', '');
        $stage = (string) $request->query('stage', '');
        $site  = (string) $request->query('site', '');

        $apps = JobApplication::query()
            ->with([
                'job:id,title,division,site_id',
                'job.site:id,code,name',
                'user:id,name',
            ])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
                      ->orWhereHas('job', function ($j) use ($q) {
                          $j->where('title', 'like', "%{$q}%")
                            ->orWhere('division', 'like', "%{$q}%")
                            ->orWhereHas('site', fn ($s) => $s->where('code', 'like', "%{$q}%"));
                      });
                });
            })
            ->when($stage, fn ($qq) => $qq->where('current_stage', $stage))
            ->when($site,  fn ($qq) => $qq->whereHas('job.site', fn ($s) => $s->where('code', $site)))
            ->latest()
            ->paginate(15);

        return view('admin.applications.index', compact('apps'));
    }

    /**
     * Admin: pindahkan stage aplikasi (RESTful, pakai route param {application})
     */
    public function moveStage(Request $request, JobApplication $application)
    {
        [$to, $status, $note, $score] = $this->validateMove($request);
        $this->applyTransition($application, $to, $status, $note, $score);
        return back()->with('ok', 'Stage dipindahkan ke: ' . $to);
    }

    /**
     * Admin: Kanban board
     * Route: GET admin/applications/board -> name: admin.applications.board
     *
     * Blade kamu mengharapkan: $stages (array) & $grouped (map stage => Collection<JobApplication>)
     */
    public function board(Request $request)
    {
        // urutan kolom yang dipakai Blade
        $stages = ['apply','psychotest','hr_iv','user_iv','final','offering','diterima','not_qualified'];

        // ambil data
        $apps = JobApplication::with([
                'job:id,title,division,site_id',
                'job.site:id,code,name',
                'user:id,name',
                'stages', // dipakai untuk score terakhir
            ])
            ->when($request->filled('job_id'), fn ($q) => $q->where('job_id', $request->job_id))
            ->when($request->filled('only'),  fn ($q) => $q->whereIn('current_stage', explode(',', $request->only)))
            ->orderBy('created_at')
            ->get();

        // normalisasi stage lama -> baru (kompatibilitas data)
        $normalize = function (?string $s): string {
            return match ($s) {
                'applied' => 'apply',
                'offer'   => 'offering',
                'hired'   => 'diterima',
                default   => $s ?? 'apply',
            };
        };

        // siapkan $grouped: key wajib ada meski kosong
        $grouped = collect($stages)->mapWithKeys(fn($s) => [$s => collect()]);

        foreach ($apps as $a) {
            $key = $normalize($a->current_stage);
            if (!in_array($key, $stages, true)) $key = 'apply';
            $grouped[$key]->push($a);
        }

        return view('admin.applications.board', compact('stages','grouped'));
    }

    /**
     * Admin: AJAX pindah stage dari Kanban (tanpa route param)
     * Route: POST admin/applications/board/move -> name: admin.applications.board.move
     * Body: { id, to | to_stage, status?, note?, score? }
     */
    public function moveStageAjax(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:job_applications,id'],
        ]);

        [$to, $status, $note, $score] = $this->validateMove($request);

        /** @var JobApplication $application */
        $application = JobApplication::with(['job.manpowerRequirement'])->findOrFail((int) $request->input('id'));

        $this->applyTransition($application, $to, $status, $note, $score);

        return response()->json([
            'ok'       => true,
            'moved_to' => $to,
            'id'       => $application->id,
        ]);
    }

    /* ============================================================
     | Helpers
     * ============================================================
     */

    /**
     * Validasi payload move (dukung name 'to' & 'to_stage')
     *
     * @return array [to, status, note, score]
     */
    protected function validateMove(Request $request): array
    {
        $allowedStages = ['apply','psychotest','hr_iv','user_iv','final','offering','diterima','not_qualified'];
        $allowedStatus = ['pending','passed','failed','no-show','reschedule'];

        // Izinkan 'to' atau 'to_stage'
        $to = $request->input('to') ?? $request->input('to_stage');

        $validated = $request->validate([
            'to'        => ['nullable', Rule::in($allowedStages)],
            'to_stage'  => ['nullable', Rule::in($allowedStages)],
            'status'    => ['nullable', Rule::in($allowedStatus)],
            'note'      => ['nullable', 'string'],
            'score'     => ['nullable', 'numeric'],
        ]);

        $to     = $to ?? 'apply';
        $status = $validated['status'] ?? 'pending';
        $note   = $validated['note'] ?? null;
        $score  = $validated['score'] ?? null;

        return [$to, $status, $note, $score];
    }

    /**
     * Terapkan perpindahan stage + side-effects (atomic)
     */
    protected function applyTransition(JobApplication $application, string $to, string $status = 'pending', ?string $note = null, $score = null): void
    {
        DB::transaction(function () use ($application, $to, $status, $note, $score) {
            // catat stage baru (timeline)
            ApplicationStage::create([
                'application_id' => $application->id,
                'stage_key'      => $to,
                'status'         => $status ?: 'pending',
                'score'          => $score !== null ? (float) $score : null,
                'payload'        => ['note' => $note],
            ]);

            // update current stage
            $application->update(['current_stage' => $to]);

            // efek samping status keseluruhan + headcount
            if ($to === 'diterima') {
                $job = $application->job()->with('manpowerRequirement')->first();
                if ($job && $job->manpowerRequirement) {
                    $job->manpowerRequirement->increment('filled_headcount');
                    if ($job->manpowerRequirement->filled_headcount >= $job->manpowerRequirement->budget_headcount) {
                        $job->update(['status' => 'closed']);
                    }
                }
                $application->update(['overall_status' => 'hired']); // tetap pakai 'hired' untuk kompatibilitas status global
            } elseif ($to === 'not_qualified') {
                $application->update(['overall_status' => 'not_qualified']);
            } else {
                if ($application->overall_status !== 'active') {
                    $application->update(['overall_status' => 'active']);
                }
            }
        });
    }
}
