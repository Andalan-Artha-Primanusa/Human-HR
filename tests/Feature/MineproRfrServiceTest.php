<?php

namespace Tests\Feature;

use App\Services\MineproRfrService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MineproRfrServiceTest extends TestCase
{
    public function test_process_list_normalizes_flexible_process_names(): void
    {
        Config::set('services.minepro.rfr_process_url', 'https://minepro.test/rfr-process-list');
        Config::set('services.minepro.api_key', 'test-key');
        Config::set('services.minepro.basic_username', 'user');
        Config::set('services.minepro.basic_password', 'pass');

        Http::fake([
            'minepro.test/*' => Http::response([
                'status' => 'success',
                'results' => [[
                    [
                        'RFRRefID' => '0001/AAP-BGG/RFR/NS/06/2026',
                        'NIK' => '3174082608890004',
                        'Process' => 'Ground Test 2',
                        'Result' => 'Lulus',
                    ],
                ]],
            ], 200),
        ]);

        $rows = app(MineproRfrService::class)->processList('2026-06-01', '2026-06-30');

        $this->assertSame('ground_test', $rows[0]['stage']);
    }
}
