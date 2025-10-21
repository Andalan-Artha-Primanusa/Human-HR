<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('company_id')
              ->nullable()
              ->constrained('companies')
              ->nullOnDelete()
              ->cascadeOnUpdate();
            $t->string('code');

            $t->string('title');
            $t->foreignUuid('site_id')
              ->nullable()
              ->constrained('sites')
              ->nullOnDelete()
              ->cascadeOnUpdate();
            $t->string('division')->nullable()->index();
            $t->string('level')->nullable();
            $t->enum('employment_type', ['intern', 'contract', 'fulltime'])->default('fulltime');
            $t->unsignedInteger('openings')->default(1);
            $t->enum('status', ['draft', 'open', 'closed'])->default('open');
            $t->text('description')->nullable();
            $t->timestamps();
            $t->unique(['company_id', 'code'], 'jobs_company_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
