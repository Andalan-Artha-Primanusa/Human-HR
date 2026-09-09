<?php

namespace App\Jobs;

use App\Services\MineproRfrImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMineproRfrJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly string $startDate,
        public readonly ?string $endDate = null,
    ) {
        $this->onQueue('minepro');
    }

    public function handle(MineproRfrImportService $importService): void
    {
        $result = $importService->import($this->startDate, $this->endDate);

        Log::info('MinePro RFR sync finished.', $result);
    }
}
