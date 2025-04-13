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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->unique();
            $table->string('billing_email')->nullable();
            $table->string('phone');
            $table->string('alt_phone')->nullable();
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('warehouse_location')->nullable();
            $table->string('preferred_pickup_time')->nullable();

            $table->string('contact_person_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_id')->nullable();

            $table->string('website_url')->nullable();
            $table->json('social_media_links')->nullable();
            $table->json('bank_account_info')->nullable();

            $table->enum('delivery_mode', ['pickup', 'delivery', 'both'])->default('both');
            $table->string('payment_terms')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->string('integration_id')->nullable();

            $table->enum('onboarding_stage', ['pending', 'active', 'verified'])->default('pending');
            $table->timestamp('last_active_at')->nullable();
            // $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->float('rating')->nullable();

            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
