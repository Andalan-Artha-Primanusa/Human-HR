<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Notifications\InterviewScheduled;

class InterviewController extends Controller
{
    /**
     * ADMIN: daftar interview (optional untuk sidebar).
     * Filter sederhana: q by job title/candidate name (optional).
     */
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');

        $interviews = Interview::with([
            'application.user:id,name,email',
            'application.job:id,title,division,site_id',
            'application.job.site:id,code,name',
        ])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->whereHas('application.job',  fn($j) => $j->where('title', 'like', "%{$q}%"))
                        ->orWhereHas('application.user', fn($u) => $u->where('name',  'like', "%{$q}%"));
                });
            })
            ->orderBy('start_at')
            ->paginate(20);

        return view('admin.interviews.index', compact('interviews', 'q'));
    }


    /**
     * ADMIN: buat jadwal interview untuk satu application (route: POST admin/interviews/{application})
     */
    public function store(Request $request, string $application)
    {
        /** @var JobApplication $app */
        $app = JobApplication::with(['user', 'job.site'])->whereKey($application)->firstOrFail();

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:200'],
            'mode'          => ['required', Rule::in(['online', 'onsite'])],
            'location'      => ['nullable', 'string', 'max:255'],
            'meeting_link'  => ['nullable', 'string', 'max:255'],
            'start_at'      => ['required', 'date'],
            'end_at'        => ['required', 'date', 'after:start_at'],
            'panel'         => ['nullable', 'array'],
            'panel.*'       => ['nullable', 'string', 'max:120'], // nama/role interviewer
            'notes'         => ['nullable', 'string'],
        ]);

        // Simpan
        $iv = new Interview();
        $iv->id             = (string) Str::uuid();
        $iv->application_id = $app->id;
        $iv->title          = $data['title'];
        $iv->mode           = $data['mode'];
        $iv->location       = $data['location'] ?? null;
        $iv->meeting_link   = $data['meeting_link'] ?? null;
        $iv->start_at       = $data['start_at'];
        $iv->end_at         = $data['end_at'];
        $iv->panel          = !empty($data['panel']) ? array_values(array_filter($data['panel'])) : null;
        $iv->notes          = $data['notes'] ?? null;
        $iv->save();

        // Kirim notifikasi ke kandidat (database notification)
        if ($app->user) {
            $app->user->notify(new InterviewScheduled($iv));
        }

        return redirect()
            ->route('admin.interviews.index')
            ->with('ok', 'Jadwal interview dibuat & pemberitahuan dikirim.');
    }

    /**
     * ADMIN: update jadwal (opsional jika kamu pakai form edit)
     */
    public function update(Request $request, string $interview)
    {
        $iv = Interview::with(['application.user', 'application.job.site'])->whereKey($interview)->firstOrFail();

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:200'],
            'mode'          => ['required', Rule::in(['online', 'onsite'])],
            'location'      => ['nullable', 'string', 'max:255'],
            'meeting_link'  => ['nullable', 'string', 'max:255'],
            'start_at'      => ['required', 'date'],
            'end_at'        => ['required', 'date', 'after:start_at'],
            'panel'         => ['nullable', 'array'],
            'panel.*'       => ['nullable', 'string', 'max:120'],
            'notes'         => ['nullable', 'string'],
        ]);

        $iv->fill([
            'title'        => $data['title'],
            'mode'         => $data['mode'],
            'location'     => $data['location'] ?? null,
            'meeting_link' => $data['meeting_link'] ?? null,
            'start_at'     => $data['start_at'],
            'end_at'       => $data['end_at'],
            'panel'        => !empty($data['panel']) ? array_values(array_filter($data['panel'])) : null,
            'notes'        => $data['notes'] ?? null,
        ])->save();

        return back()->with('ok', 'Jadwal interview diperbarui.');
    }

    /**
     * ADMIN: hapus jadwal
     */
    public function destroy(Request $request, string $interview)
    {
        $iv = Interview::whereKey($interview)->firstOrFail();
        $iv->delete();

        return back()->with('ok', 'Jadwal interview dihapus.');
    }
}
