<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('offers', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('application_id')->constrained('job_applications')->cascadeOnDelete();
      $t->enum('status',['draft','sent','accepted','declined'])->default('draft');
      $t->json('salary')->nullable();        // {gross:..., allowance:...}
      $t->text('body_template')->nullable(); // rendered HTML
      $t->string('signed_path')->nullable(); // final signed file path
      $t->timestamps();
      $t->unique('application_id');
    });
  }
  public function down(): void { Schema::dropIfExists('offers'); }
};
