<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mcu_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default Template');
            $table->string('company_name')->nullable();
            $table->string('city')->nullable();
            $table->string('project_name')->nullable();
            $table->string('vendor_name')->nullable();
            $table->text('vendor_address')->nullable();
            $table->string('subject')->nullable();
            $table->string('for_text')->nullable();
            $table->string('bu_name')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('matrix_owner')->nullable();
            $table->string('package')->nullable();
            $table->text('notes')->nullable();
            $table->text('result_emails')->nullable();
            $table->string('signer_name')->nullable();
            $table->string('signer_title')->nullable();
            $table->string('footer_company_name')->nullable();
            $table->text('footer_address')->nullable();
            $table->string('footer_email')->nullable();
            $table->string('footer_website')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcu_templates');
    }
};
