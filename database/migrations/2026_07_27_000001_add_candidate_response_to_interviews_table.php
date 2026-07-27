<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->string('candidate_response_status', 40)->default('pending')->after('notes');
            $table->text('candidate_response_note')->nullable()->after('candidate_response_status');
            $table->timestamp('candidate_response_at')->nullable()->after('candidate_response_note');
            $table->timestamp('candidate_reschedule_time')->nullable()->after('candidate_response_at');
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropColumn([
                'candidate_response_status',
                'candidate_response_note',
                'candidate_response_at',
                'candidate_reschedule_time',
            ]);
        });
    }
};
