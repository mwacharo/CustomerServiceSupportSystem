<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;



    use HasFactory;

    protected $fillable = [
        'product_name',
        'sku_no',
        'country_specific_sku',
        'bar_code',
        'description',
        'category_id',
        'vendor_id',
        'country_id',
        'product_variant_id',
        'user_id',
        'product_type',
        'weight',
        'length',
        'width',
        'height',
        'value',
        'price',
        'discount_price',
        'tax_rate',
        'brand',
        'product_link',
        'image_urls',
        'video_urls',
        'active',
        'stock_management',
        'stock_quantity',
        'tracking_required',
        'fragile',
        'hazardous',
        'temperature_sensitive',
        'returnable',
        'packaging_type',
        'handling_instructions',
        'delivery_time_window',
        'customs_info',
        'insurance_value',
        'ratings',
        'reviews',
        'tags',
        'slug',
        'meta_title',
        'meta_description',
        'update_comment',
    ];

    // Relationships

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class)->withPivot('price', 'tax_rate', 'country_sku')->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function videos()
    {
        return $this->hasMany(ProductVideo::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }



//     public function countries()
// {
//     return $this->belongsToMany(Country::class)->withPivot('price', 'tax_rate')->withTimestamps();
// }

}
