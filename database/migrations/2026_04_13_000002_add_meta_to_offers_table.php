<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            if (! Schema::hasColumn('offers', 'meta')) {
                $table->json('meta')->nullable()->after('body_template');
            }
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            if (Schema::hasColumn('offers', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};