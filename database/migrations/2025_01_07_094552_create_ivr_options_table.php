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
        Schema::create('ivr_options', function (Blueprint $table) {



            $table->id();
        $table->integer('option_number');
        $table->string('description');
        $table->string('forward_number', 15)->nullable();
        $table->timestamps();
        $table->softDeletes();
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ivr_options');
    }
};
