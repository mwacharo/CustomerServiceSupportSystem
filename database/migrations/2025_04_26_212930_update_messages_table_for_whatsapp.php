<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->text('body')->nullable();
            $table->string('message_type')->default('text');
            $table->text('media_url')->nullable();
            $table->string('media_mime_type')->nullable();
            $table->string('message_status')->default('pending');
            $table->string('external_message_id')->nullable();
            $table->string('reply_to_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->enum('direction', ['incoming', 'outgoing'])->default('outgoing');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'from',
                'to',
                'body',
                'message_type',
                'media_url',
                'media_mime_type',
                'message_status',
                'external_message_id',
                'reply_to_message_id',
                'error_message',
                'timestamp',
                'direction',
                'sent_at',
                'delivered_at',
                'read_at',
                'failed_at',
            ]);
        });
    }
};
