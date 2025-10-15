<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('interviews', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('application_id')->constrained('job_applications')->cascadeOnDelete();
      $t->string('title');
      $t->enum('mode',['online','onsite'])->default('online');
      $t->string('location')->nullable();     // address or meeting place
      $t->string('meeting_link')->nullable(); // e.g. Zoom/Meet
      $t->dateTime('start_at');
      $t->dateTime('end_at');
      $t->json('panel')->nullable();          // interviewer list
      $t->text('notes')->nullable();
      $t->timestamps();
      $t->index(['application_id','start_at']);
    });
  }
  public function down(): void { Schema::dropIfExists('interviews'); }
};
