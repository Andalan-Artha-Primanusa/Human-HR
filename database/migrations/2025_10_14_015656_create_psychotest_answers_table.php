<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('psychotest_answers', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('attempt_id')->constrained('psychotest_attempts')->cascadeOnDelete();
      $t->foreignUuid('question_id')->constrained('psychotest_questions')->cascadeOnDelete();
      $t->string('answer')->nullable();
      $t->boolean('is_correct')->nullable();
      $t->timestamps();
      $t->unique(['attempt_id','question_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('psychotest_answers'); }
};
