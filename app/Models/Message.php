<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'messageable_id',
        'messageable_type',
        'channel',
        'recipient_name',
        'recipient_phone',
        'content',
        'status',
        'sent_at',
        'response_payload',
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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',

        'response_payload' => 'array',
        'timestamp' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the parent messageable model (user, customer, vendor, etc.).
     */
    public function messageable()
    {
        return $this->morphTo();
    }
}
