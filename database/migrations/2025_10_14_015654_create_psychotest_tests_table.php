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
      $t->json('scoring')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('psychotest_tests'); }
};
