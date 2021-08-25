<?php

namespace App\Admin\Actions\Customer;

use App\Models\System\Transaction;
use App\Models\System\TransactionType;
use App\User;
use Encore\Admin\Admin;
use Encore\Admin\Form;

class Recharge
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
        $route = route('admin.customers.transactions', $this->id) . "?mode=recharge";
        return '<a href="'.$route.'" target="_blank" class="btn btn-xs btn-success" data-toggle="tooltip" title="Nạp tiền"">
                    <i class="fa fa-dollar"></i> '.$this->text.'
                </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}