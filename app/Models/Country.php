<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'status',
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function vendors()
{
    return $this->belongsToMany(Vendor::class)
                ->using(CountryVendor::class)
                ->withPivot('sku')
                ->withTimestamps();
}


// public function products()
// {
//     return $this->belongsToMany(Product::class)->withPivot('price', 'tax_rate', 'country_sku')->withTimestamps();
// }


    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function categories()
    {
        return $this->hasMany(Category::class);
    }
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }
    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
    public function suppliers()
    {
        return $this->hasMany(Vendor::class);
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    // public function payments()
    // {
    //     return $this->hasMany(Payment::class);
    // }
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
    // public function transactions()
    // {
    //     return $this->hasMany(Transaction::class);
    // }
    // public function reviews()
    // {
    //     return $this->hasMany(Review::class);
    // }
}
