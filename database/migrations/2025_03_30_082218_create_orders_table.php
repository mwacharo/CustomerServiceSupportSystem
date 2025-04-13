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

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->foreignId('drawer_id')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_price', 18, 2)->nullable();
            $table->integer('scale')->default(1);
            $table->decimal('invoice_value', 8, 2)->nullable();
            $table->decimal('amount_paid', 18, 2)->nullable();
            $table->decimal('sub_total', 8, 2)->nullable();
            $table->string('order_no')->nullable()->index();
            $table->string('sku_no')->nullable();
            $table->string('tracking_no')->nullable();
            $table->string('waybill_no')->nullable();
            $table->text('customer_notes')->nullable();
            $table->decimal('discount', 8, 2)->default(0.00);
            $table->decimal('shipping_charges', 8, 2)->default(0.00);
            $table->decimal('charges', 8, 2)->default(0.00);
            $table->dateTime('delivery_date')->nullable();
            $table->dateTime('delivery_d')->nullable();
            $table->string('status')->default('Inprogress');
            $table->string('delivery_status')->default('Inprogress');
            $table->foreignId('warehouse_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->text('paypal')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('mpesa_code')->nullable();
            $table->string('terms')->nullable();
            $table->string('template_name')->nullable();
            $table->string('platform')->default('upload');
            $table->string('route')->nullable();
            $table->text('cancel_notes')->nullable();
            $table->boolean('is_return_waiting_for_approval')->default(0);
            $table->boolean('is_salesreturn_allowed')->default(0);
            $table->boolean('is_test_order')->default(0);
            $table->boolean('is_emailed')->default(0);
            $table->boolean('is_dropshipped')->default(0);
            $table->boolean('is_cancel_item_waiting_for_approval')->default(0);
            $table->boolean('track_inventory')->default(1);
            $table->boolean('confirmed')->default(0);
            $table->boolean('delivered')->default(0);
            $table->boolean('returned')->default(0);
            $table->boolean('cancelled')->default(0);
            $table->boolean('invoiced')->default(0);
            $table->boolean('packed')->default(0);
            $table->boolean('printed')->default(0);
            $table->integer('print_count')->default(0);
            $table->boolean('sticker_printed')->default(0);
            $table->boolean('prepaid')->default(0);
            $table->boolean('paid')->default(0);
            $table->decimal('weight', 10, 2)->default(0.00);
            $table->integer('return_count')->default(0);
            $table->dateTime('dispatched_on')->nullable();
            $table->date('return_date')->nullable();
            $table->dateTime('delivered_on')->nullable();
            $table->dateTime('returned_on')->nullable();
            $table->dateTime('cancelled_on')->nullable();
            $table->dateTime('printed_at')->nullable();
            $table->string('print_no')->nullable();
            $table->dateTime('sticker_at')->nullable();
            $table->date('recall_date')->nullable();
            $table->text('history_comment')->nullable();
            $table->text('return_notes')->nullable();
            $table->foreignId('ou_id')->nullable();
            $table->string('pickup_address')->nullable();
            $table->string('pickup_phone')->nullable();
            $table->string('pickup_shop')->nullable();
            $table->string('pickup_city')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_category_id')->nullable()->constrained('order_categories')->nullOnDelete();
            $table->dateTime('schedule_date')->nullable();
            $table->foreignId('rider_id')->nullable()->constrained('riders')->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable();
            $table->string('checkout_id')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->decimal('distance', 8, 2)->nullable();
            $table->boolean('geocoded')->default(0);
            $table->string('loading_no')->nullable();
            $table->string('boxes')->nullable();
            $table->dateTime('archived_at')->nullable();
            $table->string('order_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
