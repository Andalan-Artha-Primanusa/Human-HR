<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Older imports stored HTML entities in the title column. Decode the
        // common repeated forms once at the data source so every consumer gets
        // the same clean title.
        for ($i = 0; $i < 10; $i++) {
            DB::table('job_listings')
                ->where('title', 'like', '%&amp;%')
                ->update([
                    'title' => DB::raw("REPLACE(REPLACE(title, '&amp;', '&'), '&AMP;', '&')"),
                ]);
        }
    }

    public function down(): void
    {
        // The original encoded value cannot be restored safely without a
        // separate backup of the job titles.
    }
};
