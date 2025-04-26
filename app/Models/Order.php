<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    



    protected $fillable = [
        'reference',
        'drawer_id',
        'client_id',
        'agent_id',
        'total_price',
        'scale',
        'invoice_value',
        'amount_paid',
        'sub_total',
        'order_no',
        'sku_no',
        'tracking_no',
        'waybill_no',
        'customer_notes',
        'discount',
        'shipping_charges',
        'charges',
        'delivery_date',
        'delivery_d',
        'status',
        'delivery_status',
        'warehouse_id',
        'country_id',
        'vendor_id',
        'paypal',
        'payment_method',
        'payment_id',
        'mpesa_code',
        'terms',
        'template_name',
        'platform',
        'route',
        'cancel_notes',
        'is_return_waiting_for_approval',
        'is_salesreturn_allowed',
        'is_test_order',
        'is_emailed',
        'is_dropshipped',
        'is_cancel_item_waiting_for_approval',
        'track_inventory',
        'confirmed',
        'delivered',
        'returned',
        'cancelled',
        'invoiced',
        'packed',
        'printed',
        'print_count',
        'sticker_printed',
        'prepaid',
        'paid',
        'weight',
        'return_count',
        'dispatched_on',
        'return_date',
        'delivered_on',
        'returned_on',
        'cancelled_on',
        'printed_at',
        'print_no',
        'sticker_at',
        'recall_date',
        'history_comment',
        'return_notes',
        'ou_id',
        'pickup_address',
        'pickup_phone',
        'pickup_shop',
        'pickup_city',
        'user_id',
        'schedule_date',
        'rider_id',
        'zone_id',
        'checkout_id',
        'longitude',
        'latitude',
        'distance',
        'geocoded',
        'loading_no',
        'boxes',
        'archived_at',
        'order_date',
        'order_category_id',
        'driver_id',
        'vehicle_id',
    ];

    protected $casts = [
        'delivery_date' => 'datetime',
        'delivery_d' => 'datetime',
        'dispatched_on' => 'datetime',
        'delivered_on' => 'datetime',
        'returned_on' => 'datetime',
        'cancelled_on' => 'datetime',
        'printed_at' => 'datetime',
        'sticker_at' => 'datetime',
        'schedule_date' => 'datetime',
        'archived_at' => 'datetime',
        'return_date' => 'date',
        'recall_date' => 'date',
        'geocoded' => 'boolean',
        'is_return_waiting_for_approval' => 'boolean',
        'is_salesreturn_allowed' => 'boolean',
        'is_test_order' => 'boolean',
        'is_emailed' => 'boolean',
        'is_dropshipped' => 'boolean',
        'is_cancel_item_waiting_for_approval' => 'boolean',
        'track_inventory' => 'boolean',
        'confirmed' => 'boolean',
        'delivered' => 'boolean',
        'returned' => 'boolean',
        'cancelled' => 'boolean',
        'invoiced' => 'boolean',
        'packed' => 'boolean',
        'printed' => 'boolean',
        'sticker_printed' => 'boolean',
        'prepaid' => 'boolean',
        'paid' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

   public function orderCategory(){

    return $this->belongsTo(OrderCategory::class);

   }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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
