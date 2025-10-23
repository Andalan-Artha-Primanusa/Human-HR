<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $t) {
            $t->uuid('id')->primary();

            // Company (opsional)
            $t->foreignUuid('company_id')
              ->nullable()
              ->constrained('companies')
              ->nullOnDelete()
              ->cascadeOnUpdate();

            // Kode unik per company
            $t->string('code');

            // Info utama
            $t->string('title');
            $t->foreignUuid('site_id')
              ->nullable()
              ->constrained('sites')
              ->nullOnDelete()
              ->cascadeOnUpdate();

            $t->string('division')->nullable()->index();
            $t->string('level')->nullable();

            $t->enum('employment_type', ['intern', 'contract', 'fulltime'])->default('fulltime')->index();
            $t->unsignedInteger('openings')->default(1);
            $t->enum('status', ['draft', 'open', 'closed'])->default('open')->index();

            // Deskripsi HTML (dirender di Blade dengan {!! !!} — sanitize di sisi admin)
            $t->text('description')->nullable();

            // ===== Tambahan =====
            // Skills untuk badge/chips (cast array di model)
            $t->json('skills')->nullable();

            // Keywords sederhana untuk search/filter
            $t->string('keywords')->nullable()->index();

            // Audit: siapa buat & update (opsional)
            $t->foreignUuid('created_by')->nullable()
              ->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $t->foreignUuid('updated_by')->nullable()
              ->constrained('users')->nullOnDelete()->cascadeOnUpdate();

            $t->timestamps();

            // Unique code per company
            $t->unique(['company_id', 'code'], 'jobs_company_code_unique');

            // Index kombinasi yang sering dipakai listing
            $t->index(['site_id', 'status'], 'jobs_site_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
