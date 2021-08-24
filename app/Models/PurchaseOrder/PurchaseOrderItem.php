<?php

namespace App\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'purchase_order_items';

    /**
     * Fields
     *
     * @var array
     */
    protected $fillable = [
        'product_image',
        'product_name',
        'product_link',
        'product_id',
        'property',
        'qty',
        'price',
        'customer_note',
        'admin_note',
        'price_range',
        'cn_code',
        'cn_order_number',
        'status',
        'order_group_id',
        'current_rate',
        'purchase_cn_transport_fee',
        'product_size',
        'product_color',
        'qty_reality',
        'weight',
        'shop_name',
        'type',
        'internal_note',
        'weight_date',
        'order_id',
        'customer_id',
        'order_at'
    ];

    // protected $casts = [
    //     'qty' => 'integer',
    //     'qty_reality' => 'integer',
    //     'price' => 'float',
    //     'current_rate' => 'float'
    // ];

    // protected $appends = [
    //     'array_property',
    //     'price_vn'
    // ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->hasOne('App\Models\PurchaseOrder\PurchaseOrder', 'id', 'order_id');
    }

    public function statusText() {
        return $this->hasOne('App\Models\PurchaseOrder\PurchaseOrderItemStatus', 'id', 'status');
    }

    // /**
    //  * @return float|int
    //  */
    // public function totalPrice()
    // {
    //     return $this->price_vn * $this->qty_reality;
    // }

    // /**
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    //  */
    // public function orderGroup()
    // {
    //     return $this->belongsTo(OrderGroup::class, 'order_group_id');
    // }

    // public function getArrayPropertyAttribute()
    // {
    //     return json_decode($this->property);
    // }

    // /**
    //  * @return float
    //  */
    // public function getPriceVnAttribute()
    // {
    //     return (float)($this->current_rate * $this->price);
    // }

    // public function customer()
    // {
    //     # code...
    //     return $this->hasOne(User::class, 'id', 'customer_id');
    // }
}
