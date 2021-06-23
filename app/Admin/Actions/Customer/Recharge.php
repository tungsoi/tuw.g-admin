<?php

namespace App\Admin\Actions\Customer;

use App\User;
use Encore\Admin\Admin;

class Recharge
{
    protected $id;
    protected $customer;

    public function __construct($id)
    {
        $this->id = $id;
        $this->customer = User::find($id);
        
    }

    protected function script()
    {
        return <<<SCRIPT

            $('.customer-recharge-{$this->id}').on('click', function () {

                $('#mdl-customer-recharge-{$this->id}').modal('toggle');

            });

        SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());
        $id = $this->id;
        $customer = $this->customer;
        return view('admin.system.customer.recharge', compact('id', 'customer'))->render();
    }

    public function __toString()
    {
        return $this->render();
    }
}