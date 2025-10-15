<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('manpower_requirements', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('job_id')->constrained('jobs')->cascadeOnDelete();
      $t->unsignedInteger('budget_headcount')->default(1);
      $t->unsignedInteger('filled_headcount')->default(0);
      $t->timestamps();
      $t->unique('job_id');
    });
  }
  public function down(): void { Schema::dropIfExists('manpower_requirements'); }
};
