<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remove stale migration records for files that no longer exist.
 * (add_created_by merged into create_compliance_safeties; add_unique_constraints replaced by recreate_unique_constraints)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('migrations')->whereIn('migration', [
            '2026_01_12_113315_add_created_by_to_compliance_safeties_table',
            '2026_02_03_100757_add_unique_constraints_to_subscribers_table',
        ])->delete();
    }

    public function down(): void
    {
        // Cannot restore deleted rows without batch numbers; leave empty.
    }
};
