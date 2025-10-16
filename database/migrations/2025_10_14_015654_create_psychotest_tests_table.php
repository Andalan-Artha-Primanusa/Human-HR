<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('psychotest_tests', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->string('name');
      $t->unsignedInteger('duration_minutes')->default(30);

      // aktif/nonaktifkan test ini (dipakai seeder/controller)
      $t->boolean('is_active')->default(false);
      $t->json('scoring')->nullable(); // contoh: {"pass_ratio": 0.6}

      $t->timestamps();

      // index bantu
      $t->index('is_active');
      $t->index('name');
    });
  }

  public function down(): void {
    Schema::dropIfExists('psychotest_tests');
  }
};
