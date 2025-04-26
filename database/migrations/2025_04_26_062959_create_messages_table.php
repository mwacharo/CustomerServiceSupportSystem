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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('messageable'); // adds messageable_id and messageable_type columns

            $table->string('channel')->nullable(); // SMS, Email, WhatsApp etc.
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone');
            $table->text('content');
            $table->string('status')->default('sent'); // sent, delivered, failed
            $table->timestamp('sent_at')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
