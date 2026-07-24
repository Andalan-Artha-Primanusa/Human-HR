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
            $table->text('motivation')->nullable()->after('overall_status');
            $table->text('work_motivation')->nullable()->after('motivation');
            $table->decimal('current_salary', 15, 2)->nullable()->after('work_motivation');
            $table->decimal('expected_salary', 15, 2)->nullable()->after('current_salary');
            $table->text('expected_facilities')->nullable()->after('expected_salary');
            $table->date('available_start_date')->nullable()->after('expected_facilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'motivation', 'work_motivation', 'current_salary',
                'expected_salary', 'expected_facilities', 'available_start_date',
            ]);
        });
    }
};
