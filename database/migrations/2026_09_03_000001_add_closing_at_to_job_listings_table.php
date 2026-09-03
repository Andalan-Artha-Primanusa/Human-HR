<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            if (! Schema::hasColumn('job_listings', 'closing_at')) {
                $table->dateTime('closing_at')->nullable()->after('status')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            if (Schema::hasColumn('job_listings', 'closing_at')) {
                $table->dropIndex(['closing_at']);
                $table->dropColumn('closing_at');
            }
        });
    }
};
