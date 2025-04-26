<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{


    public function up()

    {

        Schema::table('products', function (Blueprint $table) {

            // Drop the unique index

            $table->dropUnique(['slug']);

        });

    }


    public function down()

    {

        Schema::table('products', function (Blueprint $table) {

            // Re-add the unique index if needed

            $table->unique('slug');

        });

    }
};