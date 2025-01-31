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
            $table->string('client_name');
            $table->string('phone_number', 15)->unique();
            $table->string('alt_number', 15)->unique();
            $table->string('email')->unique();
            $table->string('status');
            // $table->department('department', ['business', 'exchange', 'refund', 'undelivered']);
            // $table->boolean('status')->default('inactive');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->string('officer_title');
            $table->string('user_id')->nullable();
            // $table->string('status')->default('inactive'); // inactive, busy, available
            $table->string('sessionId')->nullable();
            $table->string('token')->nullable();
            
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
