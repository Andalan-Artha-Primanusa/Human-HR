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
    protected function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole(['admin', 'hr', 'superadmin', 'trainer', 'karyawan']), 403, 'Forbidden.');
    }

    /**
     * ADMIN: daftar interview (optional untuk sidebar).
     * Filter sederhana: q by job title/candidate name (optional).
     */
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $payload = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $qRaw = (string) ($payload['q'] ?? '');
        $q = Str::limit(
            preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '',
            120,
            ''
        );
        $like = $q !== '' ? '%' . addcslashes($q, '\\%_') . '%' : null;

        $interviews = Interview::query()
            ->select(['id', 'application_id', 'title', 'mode', 'start_at', 'end_at', 'location', 'meeting_link', 'notes'])
            ->with([
                'application.user:id,name,email',
                'application.job:id,title,division,site_id',
                'application.job.site:id,code,name',
            ])
            ->when($like !== null, function ($qq) use ($like) {
                $qq->where(function ($w) use ($like) {
                    $w->whereHas('application.job', fn($j) => $j->where('title', 'like', $like))
                        ->orWhereHas('application.user', fn($u) => $u->where('name', 'like', $like));
                });
            })
            ->orderBy('start_at')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.interviews.index', compact('interviews', 'q'));
    }


    /**
     * ADMIN: buat jadwal interview untuk satu application (route: POST admin/interviews/{application})
     */
    public function store(Request $request, string $application)
    {
        $this->ensureAdmin($request);

        /** @var JobApplication $app */
        $app = JobApplication::query()
            ->select(['id', 'user_id', 'job_id'])
            ->with(['user:id'])
            ->whereKey($application)
            ->firstOrFail();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'mode' => ['required', Rule::in(['online', 'onsite'])],
            'location' => ['nullable', 'string', 'max:255', 'required_if:mode,onsite'],
            'meeting_link' => ['nullable', 'url', 'max:255', 'required_if:mode,online'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'panel' => ['nullable', 'array', 'max:20'],
            'panel.*' => ['nullable', 'string', 'max:120'], // nama/role interviewer
            'notes' => ['nullable', 'string'],
        ]);

        $panel = collect((array) ($data['panel'] ?? []))
            ->map(fn($v) => Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string) $v)) ?? '', 120, ''))
            ->filter(fn($v) => $v !== '')
            ->values()
            ->all();

        // Simpan
        $iv = new Interview();
        $iv->id = (string) Str::uuid();
        $iv->application_id = $app->id;
        $iv->title = $data['title'];
        $iv->mode = $data['mode'];
        $iv->location = $data['location'] ?? null;
        $iv->meeting_link = $data['meeting_link'] ?? null;
        $iv->start_at = $data['start_at'];
        $iv->end_at = $data['end_at'];
        $iv->panel = !empty($panel) ? $panel : null;
        $iv->notes = $data['notes'] ?? null;
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
        $this->ensureAdmin($request);

        $iv = Interview::query()->whereKey($interview)->firstOrFail();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'mode' => ['required', Rule::in(['online', 'onsite'])],
            'location' => ['nullable', 'string', 'max:255', 'required_if:mode,onsite'],
            'meeting_link' => ['nullable', 'url', 'max:255', 'required_if:mode,online'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'panel' => ['nullable', 'array', 'max:20'],
            'panel.*' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        $panel = collect((array) ($data['panel'] ?? []))
            ->map(fn($v) => Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string) $v)) ?? '', 120, ''))
            ->filter(fn($v) => $v !== '')
            ->values()
            ->all();

        $iv->fill([
            'title' => $data['title'],
            'mode' => $data['mode'],
            'location' => $data['location'] ?? null,
            'meeting_link' => $data['meeting_link'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'panel' => !empty($panel) ? $panel : null,
            'notes' => $data['notes'] ?? null,
        ])->save();

        return back()->with('ok', 'Jadwal interview diperbarui.');
    }

    /**
     * ADMIN: hapus jadwal
     */
    public function destroy(Request $request, string $interview)
    {
        $this->ensureAdmin($request);

        $iv = Interview::whereKey($interview)->firstOrFail();
        $iv->delete();

        return back()->with('ok', 'Jadwal interview dihapus.');
    }
}
