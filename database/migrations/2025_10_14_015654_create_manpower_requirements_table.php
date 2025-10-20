<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('manpower_requirements', function (Blueprint $t) {
      $t->uuid('id')->primary();

      // Relasi ke job & site (per-site requirement)
      $t->foreignUuid('job_id')->constrained('jobs')->cascadeOnDelete();
      $t->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();

      // Identitas aset (baru)
      $t->string('asset_name', 120)->nullable(); // contoh: "Dump Truck HD785"

      // Field perhitungan per (job, site, asset_name)
      $t->unsignedInteger('assets_count')->default(0);
      $t->decimal('ratio_per_asset', 3, 2)->default(2.50); // contoh: 2.50 / 2.60

      // Hasil akhir per baris (auto dihitung via model hook)
      $t->unsignedInteger('budget_headcount')->default(1);
      $t->unsignedInteger('filled_headcount')->default(0);

      $t->timestamps();

      // Satu baris per (job, site, asset_name)
      $t->unique(['job_id','site_id','asset_name']);

      // Index bantu
      $t->index(['site_id','asset_name']);
      $t->index(['site_id','assets_count']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('manpower_requirements');
  }
};
