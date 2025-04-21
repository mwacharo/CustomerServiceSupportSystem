<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;


    protected $fillable = [
        'subject',
        'body',
        'recipients',
        'has_attachments',
        'attachments',
        'status',
        'error_message',
    ];

    protected $casts = [
        'recipients' => 'array',
        'attachments' => 'array',
    ];

    public function emailable()
    {
        return $this->morphTo();
    }
}
