<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\SyncMineproRfrJobs;
use App\Services\MineproRfrImportService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('minepro:rfr-sync {--start-date=} {--end-date=} {--queue}', function (MineproRfrImportService $importService) {
    $startDate = $this->option('start-date')
        ?: config('services.minepro.rfr_sync_start_date')
        ?: now()->startOfMonth()->format('Y-m-d');
    $endDate = $this->option('end-date')
        ?: config('services.minepro.rfr_sync_end_date')
        ?: now()->endOfMonth()->format('Y-m-d');

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $startDate)) {
        $this->error('start-date harus format YYYY-MM-DD.');
        return self::FAILURE;
    }

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $endDate)) {
        $this->error('end-date harus format YYYY-MM-DD.');
        return self::FAILURE;
    }

    if ($this->option('queue')) {
        SyncMineproRfrJobs::dispatch($startDate, $endDate);
        $this->info("MinePro RFR sync dimasukkan ke queue untuk {$startDate} sampai {$endDate}.");
        return self::SUCCESS;
    }

    $result = $importService->import($startDate, $endDate);

    $this->info("MinePro RFR sync selesai. Created: {$result['created']}, skipped: {$result['skipped']}, failed: {$result['failed']}, total API: {$result['total']}.");

    if (! empty($result['errors'])) {
        foreach ($result['errors'] as $error) {
            $this->warn(($error['code'] ?? '-') . ': ' . ($error['message'] ?? 'Unknown error'));
        }
    }

    return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Sync approved MinePro RFR rows into job listings');
