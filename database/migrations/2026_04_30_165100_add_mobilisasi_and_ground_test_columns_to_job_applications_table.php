<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->json('mobilisasi_meta')->nullable()->after('mcu_result');
            $table->json('ground_test_meta')->nullable()->after('mobilisasi_meta');
            $table->string('ground_test_result')->nullable()->after('ground_test_meta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['mobilisasi_meta', 'ground_test_meta', 'ground_test_result']);
        });
    }
};
