<?php

namespace App\Admin\Actions\Customer;

use Encore\Admin\Admin;

class HistoryWalletWeight
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

        $route = route('admin.customers.walletWeight', $this->id);

        return '<a href="'.$route.'" target="_blank" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Lịch sử Ví cân" data-id="'.$this->id.'">
            <i class="fa fa-times"></i>
        </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}