<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('application_stages', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('application_id')->constrained('job_applications')->cascadeOnDelete();
      $t->string('stage_key'); // applied, psychotest, hr_iv, user_iv, final, offer, hired, not_qualified
      $t->enum('status',['pending','passed','failed','no-show','reschedule'])->default('pending');
      $t->decimal('score',8,2)->nullable();
      $t->json('payload')->nullable();
      $t->timestamps();
      $t->index(['application_id','stage_key']);
    });
  }
  public function down(): void { Schema::dropIfExists('application_stages'); }
};
