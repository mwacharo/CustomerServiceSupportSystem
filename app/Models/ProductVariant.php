<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'variant_name',
        'sku',
        'price',
        'stock',
        'attributes',
        'image',
        'is_active',
        'weight',
        'length',
        'width',
        'height',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
        'price' => 'float',
        'stock' => 'integer',
        'weight' => 'float',
        'length' => 'float',
        'width' => 'float',
        'height' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Optional: Normalize attributes relationship (if using pivot table)
//     public function attributeItems()
//     {
//         return $this->hasMany(ProductVariantAttribute::class);
//     }
 }
