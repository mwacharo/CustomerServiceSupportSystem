<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'client_id',
        'product_id',
        'quantity',
        'price',
        'status',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // public function  vendor()
    // {
    //     return $this->belongsTo(Vendor::class);
    // }
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
    }
    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
    }
    public function getStatusAttribute($value)
    {
        return $value === 1 ? 'active' : 'inactive';
    }
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value === 'active' ? 1 : 0;
    }
    public function getPriceAttribute($value)
    {
        return number_format($value, 2);
    }
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = str_replace(',', '', $value);
    }
    public function getQuantityAttribute($value)
    {
        return number_format($value);
    }
    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = str_replace(',', '', $value);
    }
    public function getOrderNumberAttribute($value)
    {
        return strtoupper($value);
    }
    public function setOrderNumberAttribute($value)
    {
        $this->attributes['order_number'] = strtoupper($value);
    }
   
    
}
