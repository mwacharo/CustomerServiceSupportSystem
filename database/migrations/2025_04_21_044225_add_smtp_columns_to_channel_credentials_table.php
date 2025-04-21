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
        Schema::table('channel_credentials', function (Blueprint $table) {
            // Adding new columns for email configuration
            $table->string('from_name')->nullable()->after('provider');
            $table->string('from_address')->nullable()->after('from_name');
            $table->string('smtp_host')->nullable()->after('from_address');
            $table->integer('smtp_port')->nullable()->after('smtp_host');
            $table->string('encryption')->nullable()->after('smtp_port');
            $table->string('mail_mailer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channel_credentials', function (Blueprint $table) {
            // Dropping the added columns if rollback is needed
            $table->dropColumn(['from_name', 'from_address', 'smtp_host', 'smtp_port', 'encryption']);
        });
    }
};
