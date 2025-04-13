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
            $table->string('product_name');
            $table->string('sku_no')->nullable();
            $table->string('country_specific_sku')->nullable();
            $table->string('bar_code')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('product_type')->default('physical');
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->string('brand')->nullable();
            $table->string('product_link')->nullable();
            $table->json('image_urls')->nullable();
            $table->json('video_urls')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('stock_management')->default(true);
            $table->integer('stock_quantity')->nullable();
            $table->boolean('tracking_required')->default(false);
            $table->boolean('fragile')->default(false);
            $table->boolean('hazardous')->default(false);
            $table->boolean('temperature_sensitive')->default(false);
            $table->boolean('returnable')->default(true);
            $table->string('packaging_type')->nullable();
            $table->text('handling_instructions')->nullable();
            $table->string('delivery_time_window')->nullable();
            $table->text('customs_info')->nullable();
            $table->decimal('insurance_value', 10, 2)->nullable();
            $table->unsignedTinyInteger('ratings')->nullable();
            $table->text('reviews')->nullable();
            $table->json('tags')->nullable();
            $table->string('slug')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('update_comment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
