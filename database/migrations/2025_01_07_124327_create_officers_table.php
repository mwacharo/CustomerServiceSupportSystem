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
        Schema::create('officers', function (Blueprint $table) {
           
            $table->id();
            $table->string('name');
            $table->string('phone_number', 15)->unique();
            $table->string('alt_number', 15)->unique();
            $table->string('email')->unique();
            $table->string('status');
            // $table->enum('department', ['business', 'exchange', 'refund', 'undelivered']);
            $table->boolean('availability')->default(true);

            $table->string('officer_title');
            $table->string('user_id')->nullable();
            // $table->string('sessionId')->nullable();
            // $table->string('token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('officers');
    }
};
