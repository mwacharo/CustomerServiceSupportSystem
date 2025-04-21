<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'provider',
        'api_key',
        'api_secret',
        'access_token',
        'access_token_secret',
        'auth_token',
        'client_id',
        'client_secret',
        'user_name',
        'password',
        'account_sid',
        'account_id',
        'app_id',
        'app_secret',
        'page_access_token',
        'page_id',
        'phone_number',
        'email_address',
        'webhook',
        'status',
        'value',
        'description',
        'meta',
        'credentialable_id',  // Added polymorphic field
        'credentialable_type' // Added polymorphic field
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Scope to filter by channel type
     */
    public function scopeForChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to filter by provider
     */
    public function scopeForProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Helper to get specific meta key
     */
    public function getMetaValue($key)
    {
        return $this->meta[$key] ?? null;
    }

    /**
     * Polymorphic relationship to other models (User, Company, Client, etc.)
     */
    public function credentialable()
    {
        return $this->morphTo();
    }


    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }



}
