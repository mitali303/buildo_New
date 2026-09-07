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
            if (! Schema::hasColumn('estimates', 'scheme_id')) {
                $table->string('scheme_id')->nullable()->after('estimate_no');
            }
            if (! Schema::hasColumn('estimates', 'scheme_name')) {
                $table->string('scheme_name')->nullable()->after('scheme_id');
            }
            if (! Schema::hasColumn('estimates', 'items')) {
                $table->json('items')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('estimates')) {
            return;
        }

        Schema::table('estimates', function (Blueprint $table) {
            foreach (['scheme_id', 'scheme_name', 'items'] as $column) {
                if (Schema::hasColumn('estimates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
