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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Template name (e.g., "Order Confirmation")
            $table->string('channel'); // Channel: whatsapp, email, sms, telegram
            $table->string('module')->nullable(); // Related module (e.g., Order, Client, Delivery)
            $table->text('content'); // The actual template text with placeholders
            $table->json('placeholders')->nullable(); // JSON array of placeholders
            $table->morphs('owner'); // owner_type, owner_id (polymorphic relation: User, Vendor, Admin, etc.)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
