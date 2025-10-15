<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('attachments', function (Blueprint $t) {
     $t->uuid('id')->primary();
    $t->uuidMorphs('attachable'); // sudah otomatis ada index
    $t->string('label')->nullable();
    $t->string('path');
    $t->string('mime')->nullable();
    $t->unsignedBigInteger('size_bytes')->nullable();
    $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('attachments'); }
};
