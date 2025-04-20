<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'mailable_type',  // The type of owner (e.g., App\Models\Company, App\Models\Vendor)
        'mailable_id',    // The specific owner’s ID
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
    ];

    // Automatically encrypt password when saving
    public function setMailPasswordAttribute($value)
    {
        $this->attributes['mail_password'] = encrypt($value);
    }

    // Automatically decrypt password when retrieving
    public function getMailPasswordAttribute($value)
    {
        return decrypt($value);
    }

    // This defines the polymorphic relationship
    public function mailable()
    {
        return $this->morphTo();
    }
}
