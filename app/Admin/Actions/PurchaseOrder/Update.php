<?php

namespace App\Admin\Actions\PurchaseOrder;

use App\Models\System\Transaction;
use App\Models\System\TransactionType;
use App\User;
use Encore\Admin\Admin;
use Encore\Admin\Form;

class Update
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
        $route = route('admin.purchase_orders.edit_data', $this->id);
        return '<a href="'.$route.'" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Chỉnh sửa"">
                    <i class="fa fa-edit"></i>
                </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}