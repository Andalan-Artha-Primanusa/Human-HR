<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidate_trainings', function (Blueprint $t) {
            $t->string('certificate_name')->nullable()->after('certificate_path');
            $t->date('cert_valid_from')->nullable()->after('certificate_name');
            $t->date('cert_valid_to')->nullable()->after('cert_valid_from');
            $t->boolean('cert_no_expiry')->default(false)->after('cert_valid_to');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_trainings', function (Blueprint $t) {
            $t->dropColumn(['certificate_name', 'cert_valid_from', 'cert_valid_to', 'cert_no_expiry']);
        });
    }
};
