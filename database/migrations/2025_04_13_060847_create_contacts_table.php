<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->morphs('contactable'); // Adds contactable_id (unsignedBigInteger) and contactable_type (string)

            $table->string('name');
            $table->string('email')->nullable(); // Make nullable, because not all contacts might have email
            $table->string('phone')->nullable();
            $table->string('alt_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();

            $table->string('country_name')->nullable();
            $table->string('state_name')->nullable();
            // Optional if you want actual relationships:
            // $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            // $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();

            $table->string('type')->nullable(); // customer, vendor, employee, etc
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();

            // Socials
            $table->string('whatsapp')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('telegram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('wechat')->nullable();
            $table->string('snapchat')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('youtube')->nullable();
            $table->string('pinterest')->nullable();
            $table->string('reddit')->nullable();

            $table->boolean('consent_to_contact')->default(false);
            $table->timestamp('consent_given_at')->nullable();

            $table->json('tags')->nullable();
            $table->string('profile_picture')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('status')->default(true); // active/inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
