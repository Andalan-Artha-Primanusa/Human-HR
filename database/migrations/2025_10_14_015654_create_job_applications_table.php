<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('job_applications', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('job_id')->constrained('jobs')->cascadeOnDelete();
      $t->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $t->string('current_stage')->default('applied'); // applied, psychotest, hr_iv, user_iv, final, offer, hired, not_qualified
      $t->enum('overall_status',['active','hired','not_qualified'])->default('active');
      $t->timestamps();
      $t->unique(['job_id','user_id']); // prevent double apply
      $t->index('current_stage');
    });
  }
  public function down(): void { Schema::dropIfExists('job_applications'); }
};
