<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('application_stages', function (Blueprint $t) {
      $t->uuid('id')->primary();

      $t->foreignUuid('application_id')
        ->constrained('job_applications')
        ->cascadeOnDelete();

      // Tahap proses
      $t->string('stage_key'); // applied, psychotest, hr_iv, user_iv, final, offer, hired, not_qualified
      $t->enum('status', ['pending','passed','failed','no-show','reschedule'])->default('pending');

      // Skor & payload bebas
      $t->decimal('score', 8, 2)->nullable();
      $t->json('payload')->nullable();

      // ===== Tambahan penting =====
      // User yang TERAKHIR mengubah stage ini (untuk "Diubah oleh")
      $t->foreignUuid('acted_by')->nullable()->constrained('users')->nullOnDelete();
      // (Opsional) Pembuat awal stage
      $t->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
      // Catatan proses (opsional)
      $t->text('notes')->nullable();

      $t->timestamps();

      // Index
      $t->index(['application_id','stage_key']);
      $t->index('acted_by');
      $t->index('user_id');
    });
  }

  public function down(): void {
    Schema::dropIfExists('application_stages');
  }
};
