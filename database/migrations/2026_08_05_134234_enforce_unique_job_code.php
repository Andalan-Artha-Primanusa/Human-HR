<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Code lowongan harus unik GLOBAL (tidak peduli company).
     * - Dedupe dulu data existing (suffix -2, -3 dst.)
     * - Drop index (company_id, code)
     * - Tambah unique index pada `code`
     */
    public function up(): void
    {
        $this->dedupeExistingCodes();

        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropUnique('jobs_company_code_unique');
        });

        Schema::table('job_listings', function (Blueprint $table) {
            $table->unique('code', 'jobs_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropUnique('jobs_code_unique');
        });

        Schema::table('job_listings', function (Blueprint $table) {
            $table->unique(['company_id', 'code'], 'jobs_company_code_unique');
        });
    }

    /** Beri suffix -2, -3, dst. pada code yang duplikat (pertahankan baris pertama). */
    private function dedupeExistingCodes(): void
    {
        if (! Schema::hasTable('job_listings')) {
            return;
        }

        $codes = DB::table('job_listings')
            ->select('code')
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('code');

        foreach ($codes as $code) {
            $ids = DB::table('job_listings')
                ->where('code', $code)
                ->orderBy('created_at')
                ->orderBy('id')
                ->pluck('id');

            // Baris pertama dipertahankan apa adanya
            $suffix = 2;
            foreach ($ids->slice(1) as $id) {
                $candidate = $code . '-' . $suffix;
                while (DB::table('job_listings')->where('code', $candidate)->exists()) {
                    $suffix++;
                    $candidate = $code . '-' . $suffix;
                }
                DB::table('job_listings')
                    ->where('id', $id)
                    ->update(['code' => $candidate]);
                $suffix++;
            }
        }
    }
};
