<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('estimates') || ! Schema::hasTable('scheme_step1')) {
            return;
        }

        $estimates = DB::table('estimates')
            ->whereNull('scheme_id')
            ->whereNotNull('project_name')
            ->get(['id', 'project_name', 'total_area']);

        foreach ($estimates as $estimate) {
            $scheme = DB::table('scheme_step1')
                ->where('Name', $estimate->project_name)
                ->first(['ID', 'Name']);

            $updates = [
                'scheme_id' => $scheme?->ID,
                'scheme_name' => $scheme?->Name ?: $estimate->project_name,
            ];

            if (Schema::hasColumn('estimates', 'built_up_area') && (float) $estimate->total_area > 0) {
                $updates['built_up_area'] = $estimate->total_area;
            }

            DB::table('estimates')->where('id', $estimate->id)->update($updates);
        }
    }

    public function down(): void
    {
        // Legacy data repair is intentionally not reversed.
    }
};
