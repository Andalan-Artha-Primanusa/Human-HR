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
            'hired' => 'Hired',
            'not_qualified' => 'TIDAK lOLOS',
        ];

        // Filter khusus:
        // Pelamar -> lihat lamaran sendiri
        // Karyawan/Trainer -> lihat lamaran di mana mereka diassign sebagai panel interview
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
            ->where(function ($query) use ($user) {
                if (in_array($user->role, ['karyawan', 'trainer'])) {
                    // Karyawan/Trainer -> lihat lamaran di mana mereka diassign sebagai panel interview
                    $query->whereHas('interviews', function ($q) use ($user) {
                        $q->whereJsonContains('panel', $user->name);
                    });
                } else {
                    // Pelamar (dan role lainnya jika nyasar) -> wajib hanya lihat milik sendiri
                    $query->where('user_id', $user->id);
                }
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $grouped = collect($stages)->mapWithKeys(fn($s, $k) => [$k => collect()]);
        foreach ($apps as $a) {
            $key = $a->current_stage ?? 'applied';
            if (!array_key_exists($key, $stages)) $key = 'applied';
            $grouped[$key]->push($a);
        }

        return view('kanban.board', [
            'stages' => $stages,
            'grouped' => $grouped,
            'isKaryawanOrTrainer' => in_array($user->role, ['karyawan','trainer','pelamar']),
        ]);
    }
}
