<?php

namespace App\Admin\Actions\PaymentOrder;

use App\Models\System\Transaction;
use App\Models\System\TransactionType;
use App\User;
use Encore\Admin\Admin;
use Encore\Admin\Form;

class TransportCodeLog
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {
        return <<<SCRIPT
        SCRIPT;
    }

    protected function render()
    {
        $route = "#";
        return '<a href="'.$route.'" class="btn btn-xs btn-success" data-toggle="tooltip" title="Lịch sử thay đổi"">
                    <i class="fa fa-history"></i>
                </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}