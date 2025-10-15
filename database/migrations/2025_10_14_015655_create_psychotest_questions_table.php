<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('psychotest_questions', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('test_id')->constrained('psychotest_tests')->cascadeOnDelete();
      $t->enum('type',['mcq','truefalse'])->default('mcq');
      $t->text('question');
      $t->json('options')->nullable();     // for mcq
      $t->string('answer_key')->nullable();
      $t->decimal('weight',5,2)->default(1);
      $t->unsignedInteger('order_no')->default(0);
      $t->timestamps();
      $t->index(['test_id','order_no']);
    });
  }
  public function down(): void { Schema::dropIfExists('psychotest_questions'); }
};
