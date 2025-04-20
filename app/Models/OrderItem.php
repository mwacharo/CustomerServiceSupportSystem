<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_items';

    protected $fillable = [
        'price',
        'total_price',
        'quantity',
        'sku_no',
        'quantity_sent',
        'quantity_delivered',
        'quantity_returned',
        'quantity_remaining',
        'shipped',
        'sent',
        'delivered',
        'returned',
        'product_rate',
        'quantity_tobe_delivered',
        'product_id',
        'sku_id',
        'order_id',
        'seller_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'product_rate' => 'decimal:2',
        'sent' => 'boolean',
        'delivered' => 'boolean',
        'returned' => 'boolean',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // public function sku()
    // {
    //     return $this->belongsTo(Sku::class);
    // }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
