<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;

class KanbanController extends Controller
{
    /** Kanban board untuk trainer saja */
    public function mine(Request $request)
    {
        $user = auth()->user();

        // Employee tidak boleh akses kanban, redirect ke dashboard
        if ($user->role === 'karyawan') {
            return redirect()->route('dashboard')->with('error', 'Akses tidak diizinkan untuk employee.');
        }

        // Trainer hanya lihat: user interview, trainer interview, ground test
        if ($user->role === 'trainer') {
            $stages = [
                'user_iv' => 'User Interview',
                'user_trainer_iv' => 'User/Trainer Interview',
                'ground_test' => 'Ground Test',
            ];
        } else {
            // Role lain (jika ada) lihat semua
            $stages = [
                'applied' => 'Applied',
                'screening' => 'Screening CV/Berkas Lamaran',
                'psychotest' => 'Psikotest',
                'hr_iv' => 'HR Interview',
                'user_iv' => 'User Interview',
                'user_trainer_iv' => 'User/Trainer Interview',
                'offer' => 'OL',
                'mcu' => 'MCU',
                'mobilisasi' => 'Mobilisasi',
                'ground_test' => 'Ground Test',
                'onsite' => 'Onsite',
                'hired' => 'Hired',
                'not_qualified' => 'TIDAK lOLOS',
            ];
        }

        // Query applications
        $apps = JobApplication::with([
            'job:id,title,division,site_id',
            'job.site:id,code,name',
            'user:id,name,email,role',
            'poh:id,name',
            'stages.actor:id,name',
            'stages.user:id,name',
            'feedbacks',
            'interviews',
            'offer',
        ])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        // Group by stage
        $grouped = collect($stages)->mapWithKeys(fn($label, $key) => [$key => collect()]);
        foreach ($apps as $a) {
            $key = $a->current_stage ?? 'applied';
            // Hanya include aplikasi yang stagenya ada di daftar stages yang diizinkan
            if (array_key_exists($key, $stages)) {
                $grouped[$key]->push($a);
            }
        }

        // If JSON requested, return lightweight counts for client polling
        if ($request->boolean('json') || $request->wantsJson()) {
            $counts = $grouped->map(fn($c) => $c->count());
            return response()->json([
                'ok' => true,
                'counts' => $counts,
            ]);
        }

        return view('kanban.board', [
            'stages' => $stages,
            'grouped' => $grouped,
            'isTrainer' => $user->role === 'trainer',
        ]);
    }
}
