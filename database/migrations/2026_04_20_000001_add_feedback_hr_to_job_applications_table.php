<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->text('feedback_hr')->nullable()->after('overall_status');
            $table->string('approve_hr', 10)->nullable()->after('feedback_hr');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['feedback_hr', 'approve_hr']);
        });
    }
};