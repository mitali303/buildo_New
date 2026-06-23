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
        Schema::create('partitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade'); // Warehouse Name selection
            $table->foreignId('rack_id')->constrained('racks')->onDelete('cascade'); // Rack Name selection
            $table->string('partition_code'); // Partition Name/Code
            $table->integer('area_sqft'); // Area (sqft)
            $table->foreignId('category_id')->constrained('categories')->onDelete('set null')->nullable(); // Category (FK)
            $table->foreignId('sub_category_id')->constrained('sub_categories')->onDelete('set null')->nullable(); // Sub-category (FK)
            $table->tinyInteger('status')->default(1); // 1 = Active, 0 = Inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partitions');
    }
};
