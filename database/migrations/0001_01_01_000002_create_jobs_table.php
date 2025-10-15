<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $t) {
            $t->uuid('id')->primary();

            $t->string('code')->unique();
            $t->string('title');
            $t->foreignUuid('site_id')
              ->nullable()
              ->constrained('sites')          // references id on sites
              ->nullOnDelete()                // if site deleted -> set null
              ->cascadeOnUpdate();

            $t->string('division')->nullable()->index();
            $t->string('level')->nullable();

            $t->enum('employment_type', ['intern','contract','fulltime'])->default('fulltime');
            $t->unsignedInteger('openings')->default(1);
            $t->enum('status', ['draft','open','closed'])->default('open');

            $t->text('description')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
