<?php

namespace App\Http\Controllers;

use App\Models\PsychotestAttempt;
use App\Models\PsychotestAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsychotestController extends Controller
{
    // ====== ADMIN: daftar attempt psikotes ======
    public function index(Request $request)
    {
        $q      = (string) $request->query('q', '');
        // nilai status di query opsional: 'active' | 'finished'
        $status = (string) $request->query('status', '');

        // definisi status aktif & selesai berbasis kolom status (bukan is_active)
        $ACTIVE_STATUSES   = ['pending','in_progress'];
        $FINISHED_STATUSES = ['submitted','scored','expired','cancelled'];

        $attempts = PsychotestAttempt::with([
                'application.user:id,name',
                'application.job:id,title,site_id',
                'application.job.site:id,code',
                'test:id,name',
            ])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->whereHas('application.user', fn($u) => $u->where('name', 'like', "%{$q}%"))
                      ->orWhereHas('application.job', fn($j) => $j->where('title', 'like', "%{$q}%"));
                });
            })
            ->when($status === 'active',   fn($qq) => $qq->whereIn('status', $ACTIVE_STATUSES))
            ->when($status === 'finished', fn($qq) => $qq->whereIn('status', $FINISHED_STATUSES))
            ->latest()
            ->paginate(15);

        return view('admin.psychotests.index', compact('attempts', 'q', 'status'));
    }

    // ====== PELAMAR: tampilkan halaman tes ======
    public function show(PsychotestAttempt $attempt, Request $request)
    {
        // hanya pemilik yang boleh akses
        abort_unless($attempt->application->user_id === $request->user()->id, 403);

        // cegah retake kalau sudah selesai/expired/cancelled
        $finished = in_array($attempt->status, ['submitted','scored','expired','cancelled'], true);
        if ($finished) {
            return redirect()
                ->route('applications.mine')
                ->with('warn', 'Tes ini sudah tidak dapat diisi lagi.');
        }

        // optional: blok jika sudah kedaluwarsa
        if ($attempt->expires_at && now()->greaterThan($attempt->expires_at)) {
            $attempt->update(['status' => 'expired']);
            return redirect()->route('applications.mine')->with('warn', 'Tes sudah kedaluwarsa.');
        }

        // lock attempt pada akses pertama
        if (is_null($attempt->started_at)) {
            $attempt->update([
                'started_at' => now(),
                'status'     => 'in_progress',
            ]);
        }

        $attempt->load(['test.questions' => fn($q) => $q->orderBy('order_no')]);

        return view('psychotest.show', ['attempt' => $attempt]);
    }

    // ====== PELAMAR: submit jawaban ======
    public function submit(Request $request, PsychotestAttempt $attempt)
    {
        // hanya pemilik
        abort_unless($attempt->application->user_id === $request->user()->id, 403);

        // cegah submit ulang
        if (in_array($attempt->status, ['submitted','scored','expired','cancelled'], true)) {
            return redirect()->route('applications.mine')
                ->with('warn', 'Tes ini sudah diselesaikan.');
        }

        // expired check (opsional)
        if ($attempt->expires_at && now()->greaterThan($attempt->expires_at)) {
            $attempt->update(['status' => 'expired']);
            return redirect()->route('applications.mine')->with('warn', 'Tes sudah kedaluwarsa.');
        }

        $data = $request->validate([
            'answers' => 'required|array', // [question_id => answer_string]
        ]);

        $attempt->load(['test.questions']);
        $questions = $attempt->test->questions;

        $maxScore = 0.0;
        $score    = 0.0;

        DB::transaction(function() use ($attempt, $questions, $data, &$score, &$maxScore) {
            foreach ($questions as $q) {
                $userAns = $data['answers'][$q->id] ?? null;
                $correct = null;

                if (!is_null($userAns)) {
                    if ($q->type === 'mcq' || $q->type === 'truefalse') {
                        $correct = strcmp((string)$userAns, (string)$q->answer_key) === 0;
                    }
                }

                PsychotestAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $q->id],
                    [
                        'answer'     => is_scalar($userAns) ? (string)$userAns : null,
                        'is_correct' => $correct,
                    ]
                );

                $w = (float)$q->weight;
                $maxScore += $w;
                if ($correct === true) $score += $w;
            }

            // FINALISASI: JANGAN set is_active=false agar tidak bentrok unique lama
            $attempt->update([
                'started_at'   => $attempt->started_at ?? now(),
                'finished_at'  => now(),
                'submitted_at' => now(),
                'status'       => 'scored', // atau 'submitted' lalu proses skor async
                'score'        => $score,
                // 'is_active'  => ...  <-- sengaja tidak disentuh
            ]);

            // catat / update stage psychotest
            $stage = $attempt->application->stages()
                ->where('stage_key','psychotest')
                ->latest()
                ->first();

            $payload = ['max_score' => $maxScore];

            if ($stage) {
                $stage->update([
                    'score'   => $score,
                    'status'  => 'passed', // atau 'failed' kalau mau pakai threshold nanti
                    'payload' => $payload
                ]);
            } else {
                $attempt->application->stages()->create([
                    'stage_key' => 'psychotest',
                    'status'    => 'passed',
                    'score'     => $score,
                    'payload'   => $payload,
                ]);
            }

            // auto-move by threshold (pakai pass_ratio dari test->scoring)
            $cfg   = $attempt->test->scoring ?? [];
            $ratio = is_array($cfg) && isset($cfg['pass_ratio']) ? (float)$cfg['pass_ratio'] : 0.6;
            if ($maxScore > 0 && ($score / $maxScore) >= $ratio) {
                $attempt->application->update(['current_stage' => 'hr_iv']);
            }
        });

        return redirect()
            ->route('applications.mine')
            ->with('ok','Psikotes selesai. Skor: '.number_format($score,2));
    }
}
