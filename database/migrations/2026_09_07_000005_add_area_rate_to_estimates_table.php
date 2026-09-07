<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('estimates')) {
            return;
        }

        Schema::table('estimates', function (Blueprint $table) {
            if (! Schema::hasColumn('estimates', 'built_up_area')) {
                $table->decimal('built_up_area', 15, 2)->nullable()->after('scheme_name');
            }
            if (! Schema::hasColumn('estimates', 'rate_per_sqft')) {
                $table->decimal('rate_per_sqft', 15, 2)->nullable()->after('built_up_area');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('estimates')) {
            return;
        }

        Schema::table('estimates', function (Blueprint $table) {
            foreach (['built_up_area', 'rate_per_sqft'] as $column) {
                if (Schema::hasColumn('estimates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
