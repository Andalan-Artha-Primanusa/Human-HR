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
            $table->text('feedback_trainer')->nullable()->after('approve_hr');
            $table->string('approve_trainer', 10)->nullable()->after('feedback_trainer');
            $table->text('feedback_user')->nullable()->after('approve_trainer');
            $table->string('approve_user', 10)->nullable()->after('feedback_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['feedback_trainer', 'approve_trainer', 'feedback_user', 'approve_user']);
        });
    }
};
