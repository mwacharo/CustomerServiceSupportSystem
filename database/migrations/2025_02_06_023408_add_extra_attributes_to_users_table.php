<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraAttributesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adding new columns. Adjust the "after" columns as necessary.
            $table->string('client_name')->nullable()->after('name');
            $table->string('phone_number', 15)->unique()->nullable()->after('phone');
            $table->string('alt_number', 15)->unique()->nullable()->after('phone_number');
            $table->string('status')->nullable()->after('email');
            
            // Foreign keys (assumes departments and countries tables exist)
            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->after('status');

            $table->foreignId('country_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->after('department_id');

            $table->string('officer_title')->nullable()->after('country_id');
            $table->string('user_id')->nullable()->after('officer_title'); // If this is needed for additional reference
            $table->string('sessionId')->nullable()->after('user_id');
            $table->string('token')->nullable()->after('sessionId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the columns added in up()
            $table->dropColumn([
                'client_name',
                'phone_number',
                'alt_number',
                'status',
                'officer_title',
                'user_id',
                'sessionId',
                'token'
            ]);

            // Drop foreign keys and their columns for department_id and country_id
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');

            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
}
