<?php

namespace App\Admin\Actions\Customer;

use Encore\Admin\Admin;

class WalletWeight
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

        return '<a href="'.$route.'" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Ví cân" data-id="'.$this->id.'">
            <i class="fa fa-bolt"></i>
        </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}