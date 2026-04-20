<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('application_feedbacks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_id');
            $table->string('stage_key'); // e.g. hr_iv, user_iv, user_trainer_iv
            $table->string('role'); // hr, karyawan, trainer
            $table->text('feedback')->nullable();
            $table->enum('approve', ['yes', 'no'])->nullable();
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_feedbacks');
    }
};
