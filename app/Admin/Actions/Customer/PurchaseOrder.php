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

    protected function script()
    {
        return <<<SCRIPT

$('.customer-purchase-order').on('click', function () {

    // Your code.
    alert($(this).data('id'));

});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return '<a href="#" class="customer-purchase-order btn btn-xs btn-primary" data-toggle="tooltip" title="ĐH mua hộ" data-id="'.$this->id.'">
            <i class="fa fa-cart-arrow-down"></i>
        </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}