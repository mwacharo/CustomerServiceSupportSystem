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
        Schema::create('call_histories', function (Blueprint $table) {
            $table->id();

            $table->string('isActive')->nullable();
            $table->string('direction')->nullable(); // inbound, outbound
            $table->string('sessionId')->nullable();
            $table->string('callerNumber')->nullable();
            $table->string('destinationNumber')->nullable();
            $table->string('durationInSeconds')->nullable();
            $table->string('currencyCode')->nullable();
            $table->string('recordingUrl')->nullable();
            $table->string('amount')->nullable();
            $table->string('hangupCause')->nullable();
            $table->string('adminId')->nullable();
            $table->string('agentId')->nullable();
            $table->string('orderNo')->nullable();
            $table->longText('notes')->nullable();
            $table->string('nextCallStep')->nullable(); //welcome, main_menu,
            $table->string('conference')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_histories');
    }
};
