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
        Schema::create('mail_configs', function (Blueprint $table) {


            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mail_mailer')->default('smtp');
            $table->string('mail_host');
            $table->integer('mail_port')->default(587);
            $table->string('mail_username');
            $table->string('mail_password'); // consider encrypting
            $table->string('mail_encryption')->nullable(); // 'tls', 'ssl'
            $table->string('mail_from_address');
            $table->string('mail_from_name');
            $table->timestamps();
            $table->softDeletes();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_configs');
    }
};
