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
     * - Selalu tampilkan staff (superadmin/admin/hr)
     * - Tambahkan user yang sudah HIRED (dari job_applications / users.* bila ada)
     * - Fallback: jika tak bisa deteksi HIRED, tampilkan pelamar juga agar list tidak kosong
     */
    protected function staffOrHiredQuery()
    {
        $staffRoles = ['superadmin', 'admin', 'hr'];

        $hasUsersRole   = Schema::hasColumn('users', 'role');
        $hasUsersStatus = Schema::hasColumn('users', 'status');
        $hasUsersHired  = Schema::hasColumn('users', 'hired_at');

        $hasJA          = DB::getSchemaBuilder()->hasTable('job_applications');
        $hasJAUserId    = $hasJA && Schema::hasColumn('job_applications', 'user_id');
        $hasJACurStage  = $hasJA && Schema::hasColumn('job_applications', 'current_stage');
        $hasJAOverall   = $hasJA && Schema::hasColumn('job_applications', 'overall_status');

        $shouldFallbackPelamar = !($hasJA && $hasJAUserId && ($hasJACurStage || $hasJAOverall));

        return User::query()->where(function ($base) use (
            $staffRoles,$hasUsersRole,$hasUsersStatus,$hasUsersHired,
            $hasJA,$hasJAUserId,$hasJACurStage,$hasJAOverall,$shouldFallbackPelamar
        ) {
            // 1) STAFF
            if ($hasUsersRole) {
                $base->whereIn('users.role', $staffRoles);
            } elseif (class_exists(\Spatie\Permission\Models\Role::class)) {
                $base->whereHas('roles', fn($r) => $r->whereIn('name', $staffRoles));
            }

            // 2) PELAMAR HIRED
            $base->orWhere(function ($hired) use (
                $hasUsersStatus,$hasUsersHired,$hasJA,$hasJAUserId,$hasJACurStage,$hasJAOverall,$shouldFallbackPelamar,$hasUsersRole
            ) {
                $hired->where(function ($sig) use ($hasUsersStatus,$hasUsersHired,$hasJA,$hasJAUserId,$hasJACurStage,$hasJAOverall,$shouldFallbackPelamar,$hasUsersRole) {
                    $sig->whereRaw('1=0'); // seed supaya OR valid di dalam grup

                    // via kolom users
                    if ($hasUsersStatus) $sig->orWhereRaw("LOWER(users.status)='hired'");
                    if ($hasUsersHired)  $sig->orWhereNotNull('users.hired_at');

                    // via job_applications
                    if ($hasJA && $hasJAUserId && ($hasJACurStage || $hasJAOverall)) {
                        $sig->orWhereExists(function ($sub) use ($hasJACurStage,$hasJAOverall) {
                            $sub->from('job_applications')
                                ->select(DB::raw(1))
                                ->whereColumn('job_applications.user_id','users.id')
                                ->where(function ($w) use ($hasJACurStage,$hasJAOverall) {
                                    $w->whereRaw('1=0');
                                    if ($hasJACurStage) $w->orWhereRaw("LOWER(job_applications.current_stage)='hired'");
                                    if ($hasJAOverall)  $w->orWhereRaw("LOWER(job_applications.overall_status)='hired'");
                                });
                        });
                    }

                    // fallback: tampilkan pelamar juga jika sinyal HIRED tak tersedia
                    if ($shouldFallbackPelamar && $hasUsersRole) {
                        $sig->orWhere('users.role','pelamar');
                    }
                });
            });
        });
    }

    /**
     * INDEX
     */
    public function index(Request $request)
    {
        $q      = (string) $request->query('q', '');
        $role   = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        $users = $this->staffOrHiredQuery()
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('users.name','like',"%{$q}%")
                      ->orWhere('users.email','like',"%{$q}%");
                    if (Schema::hasColumn('users','id_employe')) {
                        $w->orWhere('users.id_employe','like',"%{$q}%");
                    }
                });
            })
            ->when($status === 'active'   && Schema::hasColumn('users','active'), fn($qq)=>$qq->where('users.active',true))
            ->when($status === 'inactive' && Schema::hasColumn('users','active'), fn($qq)=>$qq->where('users.active',false))
            ->when($role !== '', function ($qq) use ($role) {
                if (Schema::hasColumn('users','role')) $qq->where('users.role',$role);
                elseif (method_exists(User::class,'role')) $qq->role($role);
            })
            ->orderByDesc('users.created_at')
            ->paginate(20)
            ->withQueryString();

        $roleOptions = [];
        if (Schema::hasColumn('users','role')) {
            $roleOptions = User::query()->select('role')->whereNotNull('role')->distinct()->pluck('role')->all();
        } elseif (class_exists(\Spatie\Permission\Models\Role::class)) {
            $roleOptions = \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name')->all();
        }

        return view('admin.users.index', compact('users','q','role','status','roleOptions'));
    }

    public function create()
    {
        $roleOptions = $this->roleOptions();
        return view('admin.users.create', compact('roleOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','max:255','unique:users,email'],
            'password'   => ['nullable','string','min:8'],
            'role'       => ['nullable','string','max:100'],
            'active'     => ['nullable','boolean'],
            'id_employe' => ['nullable','string','max:50', Rule::unique('users','id_employe')],
        ]);

        $u = new User();
        $u->name       = $data['name'];
        $u->email      = $data['email'];
        $u->password   = Hash::make($data['password'] ?? Str::random(12));
        if (Schema::hasColumn('users','active')) $u->active = (bool)($data['active'] ?? true);
        if (Schema::hasColumn('users','role') && isset($data['role'])) $u->role = $data['role'];
        if (Schema::hasColumn('users','id_employe') && array_key_exists('id_employe',$data)) $u->id_employe = $data['id_employe'];
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
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password'   => ['nullable','string','min:8'],
            'role'       => ['nullable','string','max:100'],
            'active'     => ['nullable','boolean'],
            'id_employe' => ['nullable','string','max:50', Rule::unique('users','id_employe')->ignore($user->id)],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) $user->password = Hash::make($data['password']);
        if (Schema::hasColumn('users','active') && array_key_exists('active',$data)) $user->active = (bool)$data['active'];
        if (Schema::hasColumn('users','role') && isset($data['role'])) $user->role = $data['role'];
        if (Schema::hasColumn('users','id_employe') && array_key_exists('id_employe',$data)) $user->id_employe = $data['id_employe'];
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
     * IMPORT CSV (mendukung id_employe)
     * Header: name,email,password(optional),role(optional),active(optional),id_employe(optional)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:csv,txt','max:10240'],
        ]);

        $file = $request->file('file');
        $created = 0; $updated = 0; $errors = [];

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle);
            if (!$header) return back()->with('err','CSV kosong.');

            $header = array_map(fn($h)=>strtolower(trim($h)), $header);
            $idx = fn($key) => array_search($key, $header);

            $iName = $idx('name');
            $iEmail = $idx('email');
            $iPass = $idx('password');
            $iRole = $idx('role');
            $iActive = $idx('active');
            $iEmp = $idx('id_employe');

            if ($iName === false || $iEmail === false) {
                return back()->with('err','Header minimal: name,email');
            }

            DB::beginTransaction();
            try {
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) === 1 && trim($row[0]) === '') continue;

                    $name  = trim($row[$iName] ?? '');
                    $email = trim($row[$iEmail] ?? '');
                    $pass  = $iPass !== false ? trim((string)($row[$iPass] ?? '')) : '';
                    $role  = $iRole !== false ? trim((string)($row[$iRole] ?? '')) : '';
                    $actIn = $iActive !== false ? trim((string)($row[$iActive] ?? '')) : null;
                    $empId = $iEmp !== false ? trim((string)($row[$iEmp] ?? '')) : '';

                    if ($name === '' || $email === '') {
                        $errors[] = 'Skip: name/email kosong -> '.implode(',',$row);
                        continue;
                    }

                    $user = User::where('email',$email)->first();

                    $activeVal = null;
                    if ($actIn !== null && Schema::hasColumn('users','active')) {
                        $activeVal = in_array(strtolower($actIn), ['1','true','yes','y'], true);
                    }

                    if ($user) {
                        $user->name = $name;
                        if ($pass !== '') $user->password = Hash::make($pass);
                        if ($activeVal !== null) $user->active = $activeVal;
                        if (Schema::hasColumn('users','role') && $role !== '') $user->role = $role;
                        if (Schema::hasColumn('users','id_employe') && $empId !== '') $user->id_employe = $empId;
                        $user->save();
                        $updated++;
                    } else {
                        $user = new User();
                        $user->name  = $name;
                        $user->email = $email;
                        $user->password = Hash::make($pass !== '' ? $pass : Str::random(12));
                        if ($activeVal !== null) $user->active = $activeVal;
                        if (Schema::hasColumn('users','role') && $role !== '') $user->role = $role;
                        if (Schema::hasColumn('users','id_employe') && $empId !== '') $user->id_employe = $empId;
                        $user->save();
                        $created++;
                    }
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                fclose($handle);
                return back()->with('err','Import gagal: '.$e->getMessage());
            }

            fclose($handle);
        }

        $msg = "Import selesai. Created: {$created}, Updated: {$updated}";
        if (!empty($errors)) {
            session()->flash('import_warnings', $errors);
            $msg .= ' (beberapa baris di-skip: '.count($errors).')';
        }

        return redirect()->route('admin.users.index')->with('ok',$msg);
    }

    /**
     * EXPORT CSV (ikutkan id_employe kalau ada)
     */
    public function export(Request $request): StreamedResponse
    {
        $filename = 'users-export-'.now()->format('Ymd-His').'.csv';
        $query = $this->staffOrHiredQuery()->orderBy('users.created_at');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output','w');
            $hasRoleCol   = Schema::hasColumn('users','role');
            $hasActiveCol = Schema::hasColumn('users','active');
            $hasEmpIdCol  = Schema::hasColumn('users','id_employe');

            $headers = ['id','name','email'];
            if ($hasEmpIdCol) $headers[] = 'id_employe';
            $headers[] = 'role';
            if ($hasActiveCol) $headers[] = 'active';
            $headers[] = 'created_at';
            fputcsv($out, $headers);

            $query->chunk(500, function ($chunk) use ($out,$hasRoleCol,$hasActiveCol,$hasEmpIdCol) {
                foreach ($chunk as $u) {
                    $row = [$u->id,$u->name,$u->email];
                    if ($hasEmpIdCol) $row[] = $u->id_employe ?? '';
                    $row[] = $hasRoleCol ? ($u->role ?? '') : (method_exists($u,'getRoleNames') ? $u->getRoleNames()->implode('|') : '');
                    if ($hasActiveCol) $row[] = (int)($u->active ?? 0);
                    $row[] = optional($u->created_at)->toDateTimeString();
                    fputcsv($out, $row);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    private function roleOptions(): array
    {
        if (Schema::hasColumn('users','role')) {
            return User::query()->select('role')->whereNotNull('role')->distinct()->orderBy('role')->pluck('role')->all();
        }
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            return \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name')->all();
        }
        return [];
    }
}
