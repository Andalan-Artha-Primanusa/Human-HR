<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('api_security_logs') && ! Schema::hasColumn('api_security_logs', 'traffic_type')) {
            Schema::table('api_security_logs', function (Blueprint $table) {
                $table->string('traffic_type', 10)->default('api')->after('actor_type')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('api_security_logs') && Schema::hasColumn('api_security_logs', 'traffic_type')) {
            Schema::table('api_security_logs', function (Blueprint $table) {
                $table->dropColumn('traffic_type');
            });
        }
    }
};
