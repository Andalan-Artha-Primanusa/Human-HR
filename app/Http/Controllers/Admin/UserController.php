<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    /**
     * Base query:
     * - SELALU tampilkan staff (superadmin/admin/hr)
     * - TAMBAHKAN user yang punya job_applications HIRED
     */
    protected function staffOrHiredQuery()
    {
        $staffRoles = ['superadmin','admin','hr'];

        // cek skema yang kita pakai
        $hasUsersRole   = Schema::hasColumn('users', 'role');
        $hasUsersStatus = Schema::hasColumn('users', 'status');     // optional fallback
        $hasUsersHired  = Schema::hasColumn('users', 'hired_at');   // optional fallback

        $hasJA          = DB::getSchemaBuilder()->hasTable('job_applications');
        $hasJAUserId    = $hasJA && Schema::hasColumn('job_applications', 'user_id');
        $hasJACurStage  = $hasJA && Schema::hasColumn('job_applications', 'current_stage');
        $hasJAOverall   = $hasJA && Schema::hasColumn('job_applications', 'overall_status');

        return User::query()->where(function ($base) use (
            $staffRoles, $hasUsersRole, $hasUsersStatus, $hasUsersHired,
            $hasJA, $hasJAUserId, $hasJACurStage, $hasJAOverall
        ) {
            // 1) STAFF
            if ($hasUsersRole) {
                $base->whereIn('users.role', $staffRoles);
            } elseif (class_exists(\Spatie\Permission\Models\Role::class)) {
                $base->whereHas('roles', function ($r) use ($staffRoles) {
                    $r->whereIn('name', $staffRoles);
                });
            }

            // 2) PELAMAR YANG SUDAH HIRED
            $base->orWhere(function ($hired) use ($hasUsersStatus, $hasUsersHired, $hasJA, $hasJAUserId, $hasJACurStage, $hasJAOverall) {
                // fallback via kolom users jika ada
                if ($hasUsersStatus) {
                    $hired->whereRaw("LOWER(users.status) = 'hired'");
                }
                if ($hasUsersHired) {
                    $hired->orWhereNotNull('users.hired_at');
                }

                // sumber utama: job_applications (hanya kalau tabel & kolomnya ada)
                if ($hasJA && $hasJAUserId && ($hasJACurStage || $hasJAOverall)) {
                    $hired->orWhereExists(function ($sub) use ($hasJACurStage, $hasJAOverall) {
                        $sub->from('job_applications')
                            ->select(DB::raw(1))
                            ->whereColumn('job_applications.user_id', 'users.id')
                            ->where(function ($w) use ($hasJACurStage, $hasJAOverall) {
                                if ($hasJACurStage) {
                                    $w->orWhereRaw("LOWER(job_applications.current_stage) = 'hired'");
                                }
                                if ($hasJAOverall) {
                                    $w->orWhereRaw("LOWER(job_applications.overall_status) = 'hired'");
                                }
                            });
                    });
                }
            });
        });
    }

    /**
     * INDEX: staff + hired job_applications only.
     * Filter opsional: q (name/email), role, active/inactive (jika ada).
     */
    public function index(Request $request)
    {
        $q      = (string) $request->query('q', '');
        $role   = (string) $request->query('role', '');
        $status = (string) $request->query('status', ''); // active|inactive

        $users = $this->staffOrHiredQuery()
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('users.name', 'like', "%{$q}%")
                  ->orWhere('users.email', 'like', "%{$q}%");
            }))
            ->when($status === 'active'   && Schema::hasColumn('users', 'active'), fn($qq) => $qq->where('users.active', true))
            ->when($status === 'inactive' && Schema::hasColumn('users', 'active'), fn($qq) => $qq->where('users.active', false))
            ->when($role !== '', function ($qq) use ($role) {
                if (Schema::hasColumn('users', 'role')) {
                    $qq->where('users.role', $role);
                } elseif (method_exists(User::class, 'role')) {
                    $qq->role($role); // Spatie
                }
            })
            ->orderByDesc('users.created_at')
            ->paginate(20)
            ->withQueryString();

        // opsi role untuk dropdown
        $roleOptions = [];
        if (Schema::hasColumn('users', 'role')) {
            $roleOptions = User::query()->select('role')->whereNotNull('role')->distinct()->pluck('role')->all();
        } elseif (class_exists(\Spatie\Permission\Models\Role::class)) {
            $roleOptions = \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name')->all();
        }

        return view('admin.users.index', compact('users', 'q', 'role', 'status', 'roleOptions'));
    }

    public function create()
    {
        $roleOptions = $this->roleOptions();
        return view('admin.users.create', compact('roleOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['nullable','string','min:8'],
            'role'     => ['nullable','string','max:100'],
            'active'   => ['nullable','boolean'],
        ]);

        $u = new User();
        $u->name     = $data['name'];
        $u->email    = $data['email'];
        $u->password = Hash::make($data['password'] ?? Str::random(12));
        if (Schema::hasColumn('users','active')) $u->active = (bool)($data['active'] ?? true);
        if (Schema::hasColumn('users','role') && isset($data['role'])) $u->role = $data['role'];
        $u->save();

        if (!Schema::hasColumn('users','role') && !empty($data['role']) && method_exists($u,'syncRoles')) {
            try { $u->syncRoles([$data['role']]); } catch (\Throwable $e) {}
        }

        return redirect()->route('admin.users.index')->with('ok','User created.');
    }

    public function edit(User $user)
    {
        $roleOptions = $this->roleOptions();
        return view('admin.users.edit', compact('user','roleOptions'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8'],
            'role'     => ['nullable','string','max:100'],
            'active'   => ['nullable','boolean'],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) $user->password = Hash::make($data['password']);
        if (Schema::hasColumn('users','active') && array_key_exists('active',$data)) $user->active = (bool)$data['active'];
        if (Schema::hasColumn('users','role') && isset($data['role'])) $user->role = $data['role'];
        $user->save();

        if (!Schema::hasColumn('users','role') && method_exists($user,'syncRoles')) {
            try { !empty($data['role']) ? $user->syncRoles([$data['role']]) : $user->syncRoles([]); } catch (\Throwable $e) {}
        }

        return redirect()->route('admin.users.index')->with('ok','User updated.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('warn','Tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('ok','User deleted.');
    }

    /**
     * Export CSV: konsisten dgn index (staff + hired dari job_applications).
     */
    public function export(Request $request): StreamedResponse
    {
        $filename = 'users-export-'.now()->format('Ymd-His').'.csv';
        $query = $this->staffOrHiredQuery()->orderBy('users.created_at');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output','w');
            $hasRoleCol = Schema::hasColumn('users','role');
            $hasActive  = Schema::hasColumn('users','active');

            fputcsv($out, ['id','name','email','role','active','created_at']);

            $query->chunk(500, function ($chunk) use ($out,$hasRoleCol,$hasActive) {
                foreach ($chunk as $u) {
                    $role = '';
                    if ($hasRoleCol) {
                        $role = $u->role ?? '';
                    } elseif (method_exists($u,'getRoleNames')) {
                        $role = $u->getRoleNames()->implode('|');
                    }
                    $active = $hasActive ? (int)$u->active : '';
                    fputcsv($out, [
                        $u->id, $u->name, $u->email, $role, $active,
                        optional($u->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    private function roleOptions(): array
    {
        if (Schema::hasColumn('users', 'role')) {
            return User::query()
                ->select('role')->whereNotNull('role')->distinct()->orderBy('role')->pluck('role')->all();
        }
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            return \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name')->all();
        }
        return [];
    }
}
