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
            $table->string('phone_number', 255)->nullable()->after('forward_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ivr_options', function (Blueprint $table) {
            $table->dropColumn('phone_number');
        });
    }
};
