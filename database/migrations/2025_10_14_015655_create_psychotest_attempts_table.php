<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('psychotest_attempts', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('application_id')->constrained('job_applications')->cascadeOnDelete();
      $t->foreignUuid('test_id')->constrained('psychotest_tests')->cascadeOnDelete();
      $t->unsignedInteger('attempt_no')->default(1);
      $t->dateTime('started_at')->nullable();
      $t->dateTime('finished_at')->nullable();
      $t->decimal('score',8,2)->nullable();
      $t->boolean('is_active')->default(true);
      $t->timestamps();
      $t->index(['application_id','test_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('psychotest_attempts'); }
};
