<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Interview;
use Illuminate\Http\Request;
use App\Services\IcsService;
use App\Mail\InterviewInviteMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class InterviewController extends Controller
{
    /**
     * Admin: list interviews (untuk sidenav admin.interviews.index)
     */
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');

        $interviews = Interview::query()
            ->with([
                'application:id,job_id,user_id',
                'application.user:id,name,email',
                'application.job:id,title,division,site_id',
                'application.job.site:id,code,name',
            ])
            ->when($q, function ($qq) use ($q) {
                $qq->whereHas('application.user', fn($u) => $u->where('name', 'like', "%{$q}%"))
                   ->orWhereHas('application.job', fn($j) => $j->where('title', 'like', "%{$q}%"));
            })
            ->orderByDesc('start_at')
            ->paginate(15);

        return view('admin.interviews.index', compact('interviews', 'q'));
    }

    /**
     * Admin: buat interview + kirim ICS
     * Route: POST /admin/interviews/{application}
     */
    public function store(Request $request, JobApplication $application)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'mode'         => 'required|in:online,onsite',
            'location'     => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url|max:255',
            'start_at'     => 'required|date',
            'end_at'       => 'required|date|after:start_at',
            'panel'        => 'nullable|array',
            'panel.*.name' => 'required_with:panel|string|max:120',
            'panel.*.email'=> 'required_with:panel|email',
            'notes'        => 'nullable|string',
        ]);

        // Buat record interview terkait aplikasi
        $interview = $application->interviews()->create($data);

        // Generate ICS
        $ics = IcsService::interviewInvite(
            $interview->title,
            Carbon::parse($interview->start_at),
            Carbon::parse($interview->end_at),
            auth()->user()->email,
            $application->user->email,
            $interview->mode === 'onsite'
                ? ($interview->location ?? '')
                : ($interview->meeting_link ?? ''),
            $interview->notes ?? 'Mohon hadir tepat waktu.'
        );

        // Kirim email undangan
        Mail::to($application->user->email)->send(new InterviewInviteMail($interview, $ics));

        return back()->with('ok', 'Undangan interview dikirim (ICS terlampir).');
    }
}
