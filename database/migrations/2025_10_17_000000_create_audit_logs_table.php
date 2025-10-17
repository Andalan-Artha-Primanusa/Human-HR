<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('event', 50)->index();           // created, updated, deleted, login, etc
            $table->string('target_type')->nullable();      // App\Models\User, App\Models\Job, etc
            $table->string('target_id', 64)->nullable()->index(); // uuid / id bebas
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamps();

            // optional FK lunak
            // $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
