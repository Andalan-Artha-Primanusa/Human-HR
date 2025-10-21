<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $t) {
            $t->uuid('id')->primary();

            $t->string('code', 50)->unique();           // Kode unik perusahaan (global)
            $t->string('name');                          // Nama tampilan
            $t->string('legal_name')->nullable();        // Nama legal (opsional)

            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('website')->nullable();
            $t->string('logo_path')->nullable();

            $t->text('address')->nullable();
            $t->string('city')->nullable();
            $t->string('province')->nullable();
            $t->string('country')->nullable();

            $t->enum('status', ['active','inactive'])->default('active');
            $t->json('meta')->nullable();

            $t->timestamps();
            $t->softDeletes();

            $t->index(['name']);
            $t->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
