<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('candidate_trainings', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('candidate_profile_id')
        ->constrained('candidate_profiles')
        ->cascadeOnDelete();

      $t->string('title');                   // Nama Training / Sertifikasi
      $t->string('institution')->nullable(); // Penyelenggara / Institusi
      $t->date('period_start')->nullable();
      $t->date('period_end')->nullable();
      $t->string('certificate_path')->nullable();
      $t->unsignedTinyInteger('order_no')->default(1);

      $t->timestamps();
      $t->index(['candidate_profile_id', 'order_no']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('candidate_trainings');
  }
};
