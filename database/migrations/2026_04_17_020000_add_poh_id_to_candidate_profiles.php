<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->uuid('poh_id')->nullable()->after('user_id');
            $table->foreign('poh_id')->references('id')->on('pohs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->dropForeign(['poh_id']);
            $table->dropColumn('poh_id');
        });
    }
};
