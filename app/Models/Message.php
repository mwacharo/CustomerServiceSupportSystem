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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'response_payload' => 'array',
    ];

    /**
     * Get the parent messageable model (user, customer, vendor, etc.).
     */
    public function messageable()
    {
        return $this->morphTo();
    }
}
