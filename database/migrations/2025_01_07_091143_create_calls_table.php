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
        Schema::create('calls', function (Blueprint $table) {


            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('caller_number', 15);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('selected_option')->nullable();
            // $table->enum('status', ['ongoing', 'completed', 'failed'])->default('ongoing');
            $table->string('status');

            $table->timestamps();

         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
