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
            // $table->morphs('contactable'); // contactable_id and contactable_type

            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('alt_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            // $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            // $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->string('country_name')->nullable();
            $table->string('state_name')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type')->nullable(); // e.g. customer, vendor, employee
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
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
            $table->string('profile_picture')->nullable(); // store file path or URL
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
