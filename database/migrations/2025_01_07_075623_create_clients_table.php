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
        Schema::create('clients', function (Blueprint $table) {

            $table->id();
            $table->string('phone_number', 15)->unique();
            $table->string('alt_phone_number', 15)->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('zip_code')->nullable();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
