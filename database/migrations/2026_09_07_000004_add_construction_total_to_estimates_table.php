<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('estimates') && ! Schema::hasColumn('estimates', 'construction_total')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->decimal('construction_total', 15, 2)->default(0)->after('material_total');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('estimates') && Schema::hasColumn('estimates', 'construction_total')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->dropColumn('construction_total');
            });
        }
    }
};
