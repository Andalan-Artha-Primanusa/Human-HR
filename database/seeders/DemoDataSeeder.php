<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        /* ===============================
         * USERS (idempotent by email)
         * =============================== */
        $staff = [
            [
                'email' => 'superadmin@pt-aap.com',
                'name' => 'Super Admin',
                'role' => 'superadmin',
                'password' => 'l1nt4h123456'
            ],
            [
                'email' => 'raulmahya.it@pt-aap.com',
                'name' => 'Super Admin',
                'role' => 'superadmin',
                'password' => 'l1nt4h123456'
            ],
            [
                'email' => 'hendy.fardiansyah@pt-aap.com',
                'name' => 'HR Expert',
                'role' => 'hr',
                'password' => 'hendy.fardiansyah'
            ],
            [
                'email' => 'vidya.paramitha.putri@pt-aap.com',
                'name' => 'HR Senior',
                'role' => 'hr',
                'password' => 'vidya.paramitha.putri'
            ],
            [
                'email' => 'rizal.abu@pt-aap.com',
                'name' => 'HR Senior',
                'role' => 'hr',
                'password' => 'rizal.abu'
            ],
            [
                'email' => 'virgiawan.arya@pt-aap.com',
                'name' => 'HR Senior',
                'role' => 'hr',
                'password' => 'virgiawan.arya'
            ],
        ];

        foreach ($staff as $user) {
            User::updateOrCreate(
                ['email' => $user['email']], // kondisi unik
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make($user['password']),
                ]
            );
        }
    }
}