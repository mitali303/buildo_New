<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('estimates')) {
            return;
        }

        if (Schema::hasColumn('estimates', 'project_name') && Schema::hasColumn('estimates', 'scheme_name')) {
            DB::table('estimates')
                ->whereNull('scheme_name')
                ->whereNotNull('project_name')
                ->update(['scheme_name' => DB::raw('project_name')]);
        }

        Schema::table('estimates', function (Blueprint $table) {
            foreach ([
                'project_name',
                'floors',
                'area_per_floor',
                'total_area',
                'labour_total',
                'other_total',
                'discount',
            ] as $column) {
                if (Schema::hasColumn('estimates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        // These legacy columns are intentionally not recreated.
    }
};