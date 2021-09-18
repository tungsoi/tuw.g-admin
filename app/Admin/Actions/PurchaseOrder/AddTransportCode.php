<?php

namespace App\Admin\Actions\PurchaseOrder;

use App\Models\System\Transaction;
use App\Models\System\TransactionType;
use App\User;
use Encore\Admin\Admin;
use Encore\Admin\Form;
use App\Models\PurchaseOrder\PurchaseOrder;

class AddTransportCode
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
        $order = PurchaseOrder::select('id', 'order_number', 'transport_code')->where('id', $this->id)->first();

        if ($order) {
            return view('admin.system.purchase_order.add_transport_code_form_items', compact('order'))->render();
        }

        return view('admin.system.core.empty')->render();

    }

    public function __toString()
    {
        return $this->render();
    }
}