// database/migrations/2025_10_20_000000_create_email_verification_codes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('email_verification_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('code_hash');
            $table->dateTime('expires_at');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->dateTime('last_sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'code_hash']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('email_verification_codes');
    }
};
