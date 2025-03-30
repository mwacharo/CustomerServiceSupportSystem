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
        Schema::table('call_histories', function (Blueprint $table) {
            // Adding new columns
            $table->string('callSessionState')->nullable()->after('status');
            $table->string('callerCountryCode')->nullable()->after('callerNumber');
            $table->string('clientDialedNumber')->nullable()->after('callerNumber');
            $table->string('callerCarrierName')->nullable()->after('callerNumber');
            $table->timestamp('callStartTime')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        

        Schema::table('call_histories', function (Blueprint $table) {
            // Drop the columns added in up()
            $table->dropColumn([
                'callSessionState',
                'callerCountryCode',
                'clientDialedNumber',
                'callerCarrierName',
                'callStartTime',
            ]);
        });
    }
};
