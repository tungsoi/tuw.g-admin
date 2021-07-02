<?php

namespace App\Admin\Actions\Customer;

use Encore\Admin\Admin;

class Transaction
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
        Admin::script($this->script());

        $route = route('admin.customers.transactions', $this->id);

        return '<a href="'.$route.'" class="btn btn-xs btn-info" data-toggle="tooltip" title="Lịch sử ví">
            <i class="fa fa-google-wallet"></i>
        </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}