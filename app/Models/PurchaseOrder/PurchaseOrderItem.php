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
        'order_at',
        'outstock_at',
        'vn_receive_at',
        'user_confirm_receive',
        'user_confirm_outstock'
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

    public function getTimeline() {
        if ($this->status == 1) {
            return $this->order_at != null ? date('H:i | d-m-Y', strtotime($this->order_at)) : null;
        } else if ($this->status == 4) {
            return $this->outstock_at != null ? date('H:i | d-m-Y', strtotime($this->outstock_at)) : null;
        } else if ($this->status == 3) {
            return $this->vn_receive_at != null ? date('H:i | d-m-Y', strtotime($this->vn_receive_at)) : null;
        }

        return null;
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

    public function userConfirm() {
        return $this->hasOne('App\User', 'id', 'user_confirm_receive');
    }
}
