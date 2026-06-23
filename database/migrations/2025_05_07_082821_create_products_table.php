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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Product Name
            $table->string('sku')->unique(); // SKU/Barcode
            $table->decimal('purchase_rate', 10, 2); // Purchase Rate
            $table->decimal('sale_rate', 10, 2); // Sale Rate
            $table->decimal('gst', 5, 2); // GST
            $table->string('uom'); // Unit of Measure (UOM)
            $table->foreignId('category_id')->constrained('categories')->onDelete('set null')->nullable(); // Category
            $table->foreignId('customer_id')->constrained('customers')->onDelete('set null')->nullable(); // Customer name selection
            $table->tinyInteger('status')->default(1); // 1 = Active, 0 = Inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
