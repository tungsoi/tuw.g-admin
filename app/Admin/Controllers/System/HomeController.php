<?php

namespace App\Admin\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Setting\RoleUser;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\View;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('Bảng điều khiển')
            ->row(function (Row $row) {
                if (Admin::user()->isRole('customer')) {
                    // $row->column(12, view('admin.system.customer.info')->render());
                    $row->column(3, new InfoBox('Khách hàng', 'book', 'green', '/admin/customers', Admin::user()->name != null ? Admin::user()->name : Admin::user()->username));
                    $row->column(3, new InfoBox('Mã khách hàng', 'tag', 'red', '/admin/order_items', Admin::user()->symbol_name));
                    $row->column(3, new InfoBox('Số dư ví', 'users', 'aqua', 'admin/auth/users', number_format(Admin::user()->wallet) . " VND"));
                    $row->column(3, new InfoBox('Số dư ví cân', 'tag', 'yellow', '/admin/puchase_orders', Admin::user()->wallet_weight . " KG"));
                } else {
                    $row->column(3, new InfoBox('Họ và tên', 'book', 'green', '/admin/customers', Admin::user()->name != null ? Admin::user()->name : Admin::user()->username));
                    $row->column(3, new InfoBox('Số dư ví cân', 'tag', 'yellow', '/admin/puchase_orders', Admin::user()->wallet_weight . " KG"));
                }
            })
            ->row(function (Row $row) { 
                // if (Admin::user()->isRole('customer')) {
                //     $row->column(12, "Box tra thông tin mã vận đơn");
                // }
            });
    }
}
