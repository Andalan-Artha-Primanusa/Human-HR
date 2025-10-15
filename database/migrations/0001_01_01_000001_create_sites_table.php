<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('code')->unique();          // e.g. HO, DBK, POS, SBS
            $t->string('name');                    // e.g. Head Office, Damai Batu Kajang
            $t->string('region')->nullable();      // e.g. Kaltim, Sultra
            $t->boolean('is_active')->default(true);
            $t->json('meta')->nullable();          // tempatkan konfigurasi per-site
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
