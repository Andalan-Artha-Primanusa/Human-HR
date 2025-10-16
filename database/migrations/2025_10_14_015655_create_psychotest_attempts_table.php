<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('psychotest_attempts', function (Blueprint $t) {
      $t->uuid('id')->primary();

      // relasi utama
      $t->foreignUuid('application_id')->constrained('job_applications')->cascadeOnDelete();
      $t->foreignUuid('test_id')->constrained('psychotest_tests')->cascadeOnDelete();
      $t->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

      // info attempt
      $t->unsignedInteger('attempt_no')->default(1);

      // status & waktu
      // contoh enum di level aplikasi: pending|in_progress|submitted|scored|expired|cancelled
      $t->string('status')->default('pending')->index();
      $t->dateTime('started_at')->nullable();
      $t->dateTime('finished_at')->nullable();
      $t->dateTime('submitted_at')->nullable();
      $t->dateTime('expires_at')->nullable();

      // hasil & meta
      $t->decimal('score', 8, 2)->nullable();
      $t->boolean('is_active')->default(true); // boleh tetap ada, tapi tidak dipakai di unique
      $t->json('meta')->nullable();

      $t->timestamps();

      // index bantu
      $t->index(['application_id','test_id']);

      // 1 attempt saja per (application, test)
      $t->unique(['application_id','test_id'], 'attempt_one_per_app_test');
    });
  }

  public function down(): void {
    Schema::dropIfExists('psychotest_attempts');
  }
};
