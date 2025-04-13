<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryVendor extends Model
{
    use HasFactory;



    protected $table = 'country_vendor';

    protected $fillable = [
        'vendor_id',
        'country_id',
        'sku',
    ];
}
