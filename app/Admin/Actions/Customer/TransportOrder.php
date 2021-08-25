<?php

namespace App\Admin\Actions\Customer;

use Encore\Admin\Admin;

class TransportOrder
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {
        return <<<SCRIPT

$('.customer-transport-order').on('click', function () {

    // Your code.
    alert($(this).data('id'));

});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return '<a href="#" class="customer-transport-order btn btn-xs btn-primary" data-toggle="tooltip" title="Đơn hàng thanh toán" data-id="'.$this->id.'">
            <i class="fa fa-car"></i>
        </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}