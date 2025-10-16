<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('candidate_employments', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('candidate_profile_id')
        ->constrained('candidate_profiles')
        ->cascadeOnDelete();

      $t->string('company');                      // Nama Perusahaan
      $t->string('position_start')->nullable();   // Jabatan Awal
      $t->string('position_end')->nullable();     // Jabatan Akhir
      $t->date('period_start')->nullable();       // Mulai
      $t->date('period_end')->nullable();         // Akhir
      $t->string('reason_for_leaving')->nullable(); // Alasan Berhenti
      $t->text('job_description')->nullable();    // Deskripsi Tugas
      $t->unsignedTinyInteger('order_no')->default(1);

      $t->timestamps();
      $t->index(['candidate_profile_id', 'order_no']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('candidate_employments');
  }
};
