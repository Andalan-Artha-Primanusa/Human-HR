<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('api_security_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('request_id', 100)->index();
            $table->dateTime('occurred_at')->index();
            $table->string('actor_type', 30)->default('guest');
            $table->uuid('user_id')->nullable()->index();
            $table->char('actor_hash', 64)->nullable()->index();
            $table->string('http_method', 10);
            $table->string('route_name')->nullable()->index();
            $table->string('route_template', 500);
            $table->unsignedSmallInteger('status_code');
            $table->unsignedBigInteger('request_size')->default(0);
            $table->unsignedBigInteger('response_size')->default(0);
            $table->unsignedInteger('response_time_ms')->default(0);
            $table->boolean('authenticated')->default(false);
            $table->string('authentication_status', 30)->default('anonymous');
            $table->string('authorization_result', 30)->nullable();
            $table->unsignedInteger('request_count_10s')->default(0);
            $table->unsignedInteger('request_count_1m')->default(0);
            $table->unsignedInteger('request_count_5m')->default(0);
            $table->unsignedInteger('same_endpoint_count_1m')->default(0);
            $table->unsignedInteger('unique_endpoint_count_1m')->default(0);
            $table->unsignedInteger('failed_auth_count_5m')->default(0);
            $table->string('user_role', 50)->nullable();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->string('user_agent_category', 30)->nullable();
            $table->tinyInteger('binary_label')->nullable();
            $table->string('attack_type')->nullable();
            $table->string('scenario_id')->nullable();
            $table->timestamps();
            $table->index(['occurred_at', 'status_code']);
            $table->index(['occurred_at', 'route_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_security_logs');
    }
};
