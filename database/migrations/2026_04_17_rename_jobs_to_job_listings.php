<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Rename old jobs table to job_listings
        if (Schema::hasTable('jobs')) {
            Schema::rename('jobs', 'job_listings');
        }
    }

    public function down(): void
    {
        // Rollback: rename job_listings back to jobs
        if (Schema::hasTable('job_listings')) {
            Schema::rename('job_listings', 'jobs');
        }
    }
};
