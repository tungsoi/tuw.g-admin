<?php

namespace App\Admin\Controllers\System;

use App\Http\Controllers\Controller;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('Bảng điều khiển')
            ->row(function (Row $row) {
                if (Admin::user()->isRole('administrator')) {
                    $row->column(3, new InfoBox('Khách hàng', 'book', 'green', '/admin/customers', User::count()));
                    $row->column(3, new InfoBox('Đơn hàng', 'users', 'aqua', 'admin/auth/users', 1));
                    $row->column(3, new InfoBox('Tổng giá trị đơn hàng', 'tag', 'yellow', '/admin/puchase_orders', '10.452.500.000'));
                    $row->column(3, new InfoBox('Tổng lợi nhuận', 'tag', 'red', '/admin/order_items', '1.500.680.000'));
                }
                else if (Admin::user()->isRole('customer')) {
                    $row->column(6, new InfoBox('Đơn hàng của bạn', 'users', 'aqua', 'admin/auth/users', 1));
                    $row->column(6, new InfoBox('Tổng giá trị đơn hàng', 'tag', 'yellow', '/admin/puchase_orders', '350.000.000'));
                }
            });
    }
}
