<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->uuid('id')->primary(); // kalau mau increment pakai $table->id();
            $table->string('code', 20)->unique();             // BGG, DBK, SBS, dll
            $table->string('name', 150);
            $table->string('region', 100)->nullable();        // Kalimantan Timur, dsb
            $table->string('timezone', 64)->nullable();       // Asia/Makassar (opsional)
            $table->string('address', 255)->nullable();       // opsional
            $table->boolean('is_active')->default(true);      // default aktif
            $table->json('meta')->nullable();                 // bebas, untuk konfigurasi tambahan
            $table->text('notes')->nullable();                // dipakai di view show (opsional)

            $table->timestamps();
            $table->index(['region', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
