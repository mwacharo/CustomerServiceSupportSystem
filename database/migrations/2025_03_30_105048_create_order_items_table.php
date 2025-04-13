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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('total_price', 8, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('sku_no')->nullable();
            $table->integer('quantity_sent')->default(0);
            $table->integer('quantity_delivered')->default(0);
            $table->integer('quantity_returned')->default(0);
            $table->integer('quantity_remaining')->default(0);
            $table->boolean('shipped')->default(0);
            $table->boolean('sent')->default(0);
            $table->boolean('delivered')->default(0);
            $table->boolean('returned')->default(0);
            $table->decimal('product_rate', 8, 2)->default(0.00);
            $table->integer('quantity_tobe_delivered')->default(0);
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            // $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
