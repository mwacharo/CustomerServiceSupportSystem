<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// return new class extends Migration
class RenameAdminIdToUserIdInCallHistoriesTable extends Migration
{
    public function up()
    {
        Schema::table('call_histories', function (Blueprint $table) {
            // Add new column first
            $table->unsignedBigInteger('user_id')->nullable()->after('adminId');
        });

        // Copy data from adminId to user_id
        DB::statement('UPDATE call_histories SET user_id = adminId');

        Schema::table('call_histories', function (Blueprint $table) {
            // Drop old column
            $table->dropColumn('adminId');

            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('call_histories', function (Blueprint $table) {
            // Add back adminId
            $table->string('adminId')->nullable();
        });

        // Copy data back
        DB::statement('UPDATE call_histories SET adminId = user_id');

        Schema::table('call_histories', function (Blueprint $table) {
            // Drop foreign key and user_id
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
