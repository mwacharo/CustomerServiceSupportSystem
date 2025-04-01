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
        Schema::table('ivr_options', function (Blueprint $table) {
            //

            $table->string('status')->nullable();  
            $table->foreignId('branch_id')->nullable();  
            $table->foreignId('country_id')->nullable();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ivr_options', function (Blueprint $table) {
            //
            $table->dropColumn(['status', 'branch_id', 'country_id']);  // Dropping the columns if migration is rolled back

        });
    }
};
