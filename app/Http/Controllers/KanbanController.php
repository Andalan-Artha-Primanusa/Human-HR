<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;

class KanbanController extends Controller
{
    /** Kanban board untuk user/trainer/karyawan */
    public function mine(Request $request)
    {
        $user = auth()->user();
        
        // Hanya tampilkan stages yang relevan untuk trainer/karyawan
        $stages = [
            'user_iv' => 'User Interview',
            'user_trainer_iv' => 'User/Trainer Interview',
            'ground_test' => 'Ground Test',
        ];

        // Filter khusus:
        // Karyawan/Trainer -> lihat semua lamaran untuk bisa memberikan feedback
        // (Pelamar tidak dapat mengakses halaman ini karena middleware role:karyawan,trainer)
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

        $grouped = collect($stages)->mapWithKeys(fn($label, $key) => [$key => collect()]);
        foreach ($apps as $a) {
            $key = $a->current_stage ?? 'applied';
            // Hanya masukkan aplikasi dengan stage yang sesuai
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
            'isKaryawanOrTrainer' => in_array($user->role, ['karyawan','trainer']),
        ]);
    }
}
