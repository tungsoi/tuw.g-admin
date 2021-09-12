<?php

namespace App\Admin\Actions\PurchaseOrder;

use App\Models\System\Transaction;
use App\Models\System\TransactionType;
use App\User;
use Encore\Admin\Admin;
use Encore\Admin\Form;

class Deposite
{
    protected $id;
    protected $text;
    protected $customer;

    public function __construct($id, $text = "")
    {
        $this->id = $id;
        $this->text = $text;
    }

    protected function script()
    {
        return <<<SCRIPT
        SCRIPT;
    }

    protected function render()
    {
        $route = route('admin.purchase_orders.admin_deposite', $this->id);
        return '<a href="'.$route.'" class="btn btn-xs btn-success" data-toggle="tooltip" title="Đặt cọc"">
                    <i class="fa fa-dollar"></i>
                </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}