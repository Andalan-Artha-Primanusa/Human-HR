<?php

namespace App\Http\Controllers;

use App\Models\PsychotestAttempt;
use App\Models\PsychotestAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsychotestController extends Controller
{
    // Pelamar: tampilkan halaman tes
    public function show(PsychotestAttempt $attempt, Request $request)
    {
        abort_unless($attempt->application->user_id === $request->user()->id, 403);
        $attempt->load(['test.questions' => fn($q) => $q->orderBy('order_no')]);

        return view('psychotest.show', ['attempt' => $attempt]);
    }

    // Pelamar: submit jawaban
    public function submit(Request $request, PsychotestAttempt $attempt)
    {
        abort_unless($attempt->application->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'answers'   => 'required|array',          // [question_id => answer_string]
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
                    ['answer' => is_scalar($userAns) ? (string)$userAns : null, 'is_correct' => $correct]
                );

                $w = (float)$q->weight;
                $maxScore += $w;
                if ($correct === true) $score += $w;
            }

            $attempt->update([
                'started_at' => $attempt->started_at ?? now(),
                'finished_at'=> now(),
                'score'      => $score,
                'is_active'  => false,
            ]);

            // Simpan ke stage psychotest (buat kalau belum ada)
            $stage = $attempt->application->stages()
                ->where('stage_key','psychotest')
                ->latest()
                ->first();

            $payload = ['max_score' => $maxScore];
            if ($stage) {
                $stage->update(['score' => $score, 'status' => 'passed', 'payload' => $payload]);
            } else {
                $attempt->application->stages()->create([
                    'stage_key' => 'psychotest',
                    'status'    => 'passed',
                    'score'     => $score,
                    'payload'   => $payload,
                ]);
            }

            // Optional auto-move by threshold
            $cfg = $attempt->test->scoring ?? [];
            $ratio = is_array($cfg) && isset($cfg['pass_ratio']) ? (float)$cfg['pass_ratio'] : 0.6;
            if ($maxScore > 0 && ($score / $maxScore) >= $ratio) {
                $attempt->application->update(['current_stage' => 'hr_iv']);
            }
        });

        return redirect()->route('applications.mine')->with('ok','Psikotes selesai. Skor: '.number_format($score,2));
    }
}
