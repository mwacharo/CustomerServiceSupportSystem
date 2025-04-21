<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   



    public function up(): void
    {
        Schema::create('channel_credentials', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship
            $table->morphs('credentialable'); // Adds credentialable_id and credentialable_type

            // Core info
            $table->string('channel'); // e.g. 'email', 'sms', 'whatsapp'
            // $table->string('provider'); // e.g. 'zoho', 'twilio', 'meta', 'telegram'
            $table->string('provider')->nullable();

            // Common credentials
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('access_token')->nullable();
            $table->string('access_token_secret')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('user_name')->nullable();
            $table->string('password')->nullable();

            // Meta platforms
            $table->string('account_sid')->nullable();
            $table->string('account_id')->nullable();
            $table->string('app_id')->nullable();
            $table->string('app_secret')->nullable();
            $table->string('page_access_token')->nullable();
            $table->string('page_id')->nullable();

            // Contact details
            $table->string('phone_number')->nullable();
            $table->string('email_address')->nullable();
            $table->string('webhook')->nullable();

            // Misc
            $table->string('status')->default('active');
            $table->text('value')->nullable(); // general value or notes
            $table->string('description')->nullable();

            // Extensible metadata
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_credentials');
    }
};
