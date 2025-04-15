<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  


    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename the column
        if (Schema::hasColumn('call_histories', 'agentId')) {
            DB::statement('ALTER TABLE call_histories CHANGE agentId ivr_option_id BIGINT UNSIGNED NULL');
        }

        // Now add the foreign key constraint
        Schema::table('call_histories', function (Blueprint $table) {
            $table->foreign('ivr_option_id')->references('id')->on('ivr_options')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key
        Schema::table('call_histories', function (Blueprint $table) {
            $table->dropForeign(['ivr_option_id']);
        });

        // Rename the column back
        if (Schema::hasColumn('call_histories', 'ivr_option_id')) {
            DB::statement('ALTER TABLE call_histories CHANGE ivr_option_id agentId BIGINT UNSIGNED NULL');
        }
    }
};
