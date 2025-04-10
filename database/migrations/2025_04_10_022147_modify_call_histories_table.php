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
        // Rename the agentId column to ivr_option_id if it exists
        if (Schema::hasColumn('call_histories', 'agentId')) {
            DB::statement('ALTER TABLE call_histories CHANGE agentId ivr_option_id BIGINT(20) UNSIGNED NULL;');
        }

        Schema::table('call_histories', function (Blueprint $table) {
            // Add foreign key for ivr_option_id if it does not exist
            if (!Schema::hasColumn('call_histories', 'ivr_option_id')) {
                $table->foreignId('ivr_option_id')->nullable()->constrained('ivr_options')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_histories', function (Blueprint $table) {
            // Drop foreign key and column if ivr_option_id exists
            if (Schema::hasColumn('call_histories', 'ivr_option_id')) {
                $table->dropForeign(['ivr_option_id']);
                $table->dropColumn('ivr_option_id');
            }
        });

        // Rename ivr_option_id column back to agentId if it exists
        if (Schema::hasColumn('call_histories', 'ivr_option_id')) {
            DB::statement('ALTER TABLE call_histories CHANGE ivr_option_id agentId BIGINT(20) UNSIGNED NULL;');
        }
    }
    

};
