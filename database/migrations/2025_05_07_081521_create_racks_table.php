<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses'); // Warehouse Name selection (Foreign Key to warehouses)
            $table->string('rack_name'); // Rack Name
            $table->integer('total_area_sqft'); // Total Area (sqft)
            $table->integer('number_of_partitions'); // Number of Partitions
            $table->decimal('monthly_rent_rate', 10, 2); // Monthly Rent Rate
            $table->decimal('per_sqft_rent_rate', 10, 2); // Per Sqft Rent Rate
            $table->tinyInteger('status')->default(1); // 1 = Active, 0 = Inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racks');
    }
};
