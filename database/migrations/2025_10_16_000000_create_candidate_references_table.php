<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('candidate_references', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('candidate_profile_id')
        ->constrained('candidate_profiles')
        ->cascadeOnDelete();

      $t->string('name');                 // Nama Relasi (non-keluarga)
      $t->string('job_title')->nullable();// Jabatan
      $t->string('company')->nullable();  // Perusahaan
      $t->string('contact')->nullable();  // No. aktif / email
      $t->unsignedTinyInteger('order_no')->default(1);

      $t->timestamps();
      $t->index(['candidate_profile_id', 'order_no']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('candidate_references');
  }
};
