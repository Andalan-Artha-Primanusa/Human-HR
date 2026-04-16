<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;
use Illuminate\Support\Str;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            [
                'code'      => 'BGG',
                'name'      => 'Bengalon',
                'region'    => 'Kalimantan Timur',
                'timezone'  => 'Asia/Makassar',
                'address'   => 'Jl. Poros Bengalon',
                'is_active' => true,
                'latitude'  => -0.4567890,
                'longitude' => 117.4567890,
                'meta'      => ['type' => 'main'],
                'notes'     => 'Site utama di Kaltim',
            ],
            [
                'code'      => 'DBK',
                'name'      => 'Dibalik',
                'region'    => 'Kalimantan Timur',
                'timezone'  => 'Asia/Makassar',
                'address'   => 'Jl. Poros Dibalik',
                'is_active' => true,
                'latitude'  => -0.5678901,
                'longitude' => 117.5678901,
                'meta'      => ['type' => 'branch'],
                'notes'     => 'Site cabang',
            ],
        ];

        foreach ($sites as $data) {
            Site::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
