<?php

namespace App\Admin\Controllers\Report;

use App\Admin\Services\UserService;
use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SaleReport\SaleSalary;
use App\Models\SaleReport\SaleSalaryDetail;
use App\Models\System\Transaction;
use App\Models\TransportOrder\TransportCode;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\DB;

class SaleSalaryDetailController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Chi tiết doanh số theo khách hàng';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function detail($id)
    {
        $grid = new Grid(new SaleSalaryDetail());
        $grid->model()->where('sale_salary_id', $id);

        $grid->header(function ($query) use ($id) {
            $amount = $query->where('wallet', '<', 0)->sum('wallet');
            $html = number_format($amount);

            return "Tổng âm ví khách hàng: <h3 class='label label-danger'>".$html."</h3> VND";
        });
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT')->style('text-align: center');
        $grid->customer()->symbol_name('MKH');
        $grid->wallet('Số dư')
        ->display(function ($value) {
            return number_format($value);
        });
        $grid->po_success('Đơn Order thành công')
        ->display(function ($value) {
            return number_format($value);
        })
        ->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: wheat');

        $grid->po_payment('Doanh số')
        ->display(function ($value) {
            return number_format($value);
        })
        ->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: wheat');

        $grid->po_service_fee('Phí dịch vụ')
        ->display(function ($value) {
            return number_format($value);
        })->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: wheat');

        $grid->po_rmb('Tổng giá tệ')
        ->display(function ($value) {
            return number_format($value, 2);
        })->totalRow(function ($amount) {
            return number_format($amount, 2);
        })
        ->style('background: wheat');

        $grid->po_offer('Tổng đàm phán')
        ->display(function ($value) {
            return number_format($value);
        })->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: wheat');

        $grid->po_not_success('Đơn hàng order chưa hoàn thành')
        ->display(function ($value) {
            return number_format($value);
        })->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: antiquewhite');
        $grid->po_not_success_payment('Doanh số')
        ->display(function ($value) {
            return number_format($value);
        })->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: antiquewhite');
        $grid->po_not_success_service_fee('Phí dịch vụ')
        ->display(function ($value) {
            return number_format($value);
        })->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: antiquewhite');
        $grid->po_not_success_deposite('Tổng cọc')
        ->display(function ($value) {
            return number_format($value);
        })->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: antiquewhite');
        $grid->po_not_success_owed('Công nợ trên đơn')
        ->display(function ($value) {
            return number_format($value);
        })->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: antiquewhite');
        $grid->trs('Đơn hàng vận chuyển')
        ->display(function ($value) {
            return number_format($value);
        })->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: burlywood');
        $grid->trs_kg('Tổng KG')
        ->display(function ($value) {
            return number_format($value, 1);
        })->totalRow(function ($amount) {
            return number_format($amount, 1);
        })
        ->style('background: burlywood');
        $grid->trs_m3('Tổng M3')
        ->display(function ($value) {
            return number_format($value, 3);
        })->totalRow(function ($amount) {
            return number_format($amount, 3);
        })
        ->style('background: burlywood');
        $grid->trs_payment('Doanh thu')
        ->display(function ($value) {
            return number_format($value);
        })->totalRow(function ($amount) {
            return number_format($amount);
        })
        ->style('background: burlywood');
 
        $grid->paginate(1000);
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableTools();
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableFilter();

        Admin::script($this->offerOrderScript());

        return $grid;
    }

    public function offerOrderScript() {
        return <<<SCRIPT

        $('tfoot').each(function () {
            $(this).insertAfter($(this).siblings('thead'));
        });
SCRIPT;
    }


}