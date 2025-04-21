<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'billing_email',
        'phone',
        'alt_phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'region',
        'warehouse_location',
        'preferred_pickup_time',
        'contact_person_name',
        'business_type',
        'registration_number',
        'tax_id',
        'website_url',
        'social_media_links',
        'bank_account_info',
        'delivery_mode',
        'payment_terms',
        'credit_limit',
        'integration_id',
        'onboarding_stage',
        'last_active_at',
        // 'branch_id',
        'rating',
        'status',
        'notes',
        'user_id',
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }


    public function countries()
{
    return $this->belongsToMany(Country::class)
                ->using(CountryVendor::class)
                ->withPivot('sku')
                ->withTimestamps();
}

    // public function countries()
    // {
    //     return $this->belongsToMany(Country::class, 'country');
    // }

    // public function services ()
    // {
    //     return $this->hasMany(Service::class);
    // }

    // public function settings()
    // {
    //     return $this->belongsTo(Settings::class, 'branch_id');
    // }
    


    public function channelCredentials()
{
    return $this->morphMany(ChannelCredential::class, 'credentialable');
}

    

}
