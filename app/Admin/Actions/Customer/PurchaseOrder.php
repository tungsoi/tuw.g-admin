<?php

namespace App\Admin\Actions\Customer;

use Encore\Admin\Admin;

class PurchaseOrder
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function render()
    {
        $route = route('admin.purchase_orders.index') . "?customer_id=" . $this->id;

        return '<a href="'.$route.'" class="customer-purchase-order btn btn-xs btn-primary" data-toggle="tooltip" title="Đơn hàng mua hộ" data-id="'.$this->id.'">
            <i class="fa fa-cart-arrow-down"></i>
        </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}