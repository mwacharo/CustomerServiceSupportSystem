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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Link to products
            $table->string('variant_name')->nullable();   // Optional variant label e.g. "Red - XL"
            $table->string('sku')->unique();              // SKU for inventory
            $table->decimal('price', 10, 2);              // Variant price
            $table->integer('stock')->default(0);         // Stock quantity
            $table->json('attributes')->nullable();       // e.g., {"color": "Red", "size": "M"}
            $table->string('image')->nullable();          // Variant image
            $table->boolean('is_active')->default(true);  // Active status
            // Shipping-related fields
            $table->float('weight')->nullable();          // In kg or grams
            $table->float('length')->nullable();          // In cm
            $table->float('width')->nullable();           // In cm
            $table->float('height')->nullable();          // In cm

            $table->timestamps();
            $table->softDeletes();                        // Allow soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
