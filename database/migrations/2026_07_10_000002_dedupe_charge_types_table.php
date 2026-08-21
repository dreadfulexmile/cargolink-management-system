<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The seeder was re-run several times without being idempotent, creating duplicate
     * charge types on every run. This collapses each duplicate group down to the original
     * (lowest id) row, re-pointing any job cost lines that referenced a duplicate first.
     */
    public function up(): void
    {
        $groups = DB::table('charge_types')
            ->select('name', 'kind')
            ->groupBy('name', 'kind')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $ids = DB::table('charge_types')
                ->where('name', $group->name)
                ->where('kind', $group->kind)
                ->orderBy('id')
                ->pluck('id');

            $canonicalId = $ids->first();
            $duplicateIds = $ids->slice(1)->all();

            DB::table('job_cost_lines')
                ->whereIn('charge_type_id', $duplicateIds)
                ->update(['charge_type_id' => $canonicalId]);

            DB::table('charge_types')->whereIn('id', $duplicateIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data cleanup — the duplicate rows are gone for good.
    }
};
