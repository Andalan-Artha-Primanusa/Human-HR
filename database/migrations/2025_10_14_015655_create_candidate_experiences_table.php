<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('candidate_experiences', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('candidate_profile_id')->constrained('candidate_profiles')->cascadeOnDelete();
      $t->string('company');
      $t->string('title')->nullable();
      $t->date('start_date')->nullable();
      $t->date('end_date')->nullable();
      $t->boolean('is_current')->default(false);
      $t->text('description')->nullable();
      $t->timestamps();
      $t->index(['candidate_profile_id','start_date']);
    });
  }
  public function down(): void { Schema::dropIfExists('candidate_experiences'); }
};
