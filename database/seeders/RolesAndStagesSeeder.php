<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndStagesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['pelamar','hr','superadmin'] as $r) {
            Role::firstOrCreate(['name'=>$r, 'guard_name'=>'web']);
        }
    }
}
