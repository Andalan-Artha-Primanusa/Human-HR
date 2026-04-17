<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->uuid('poh_id')->nullable()->after('job_id');
            $table->foreign('poh_id')->references('id')->on('pohs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropForeign(['poh_id']);
            $table->dropColumn('poh_id');
        });
    }
};
