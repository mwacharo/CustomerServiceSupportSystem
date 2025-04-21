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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->longText('body');
            $table->json('recipients');
            $table->boolean('has_attachments')->default(false);
            $table->json('attachments')->nullable();
            $table->string('status')->default('sent'); // sent, failed
            $table->text('error_message')->nullable();
            $table->morphs('emailable'); // for associating to Company, User, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
