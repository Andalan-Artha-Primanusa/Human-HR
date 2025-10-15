<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('candidate_profiles', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $t->string('full_name')->nullable();
      $t->string('phone')->nullable();
      $t->date('birthdate')->nullable();
      $t->string('address')->nullable();
      $t->string('cv_path')->nullable();
      $t->json('extras')->nullable();
      $t->timestamps();
      $t->unique('user_id');
    });
  }
  public function down(): void { Schema::dropIfExists('candidate_profiles'); }
};
