<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadStatusToCallHistoriesTable extends Migration
{
    public function up(): void
    {
        Schema::table('call_histories', function (Blueprint $table) {
            $table->string('download_status')->default('pending')->after('recordingUrl');
        });
    }

    public function down(): void
    {
        Schema::table('call_histories', function (Blueprint $table) {
            $table->dropColumn('download_status');
        });
    }
}
