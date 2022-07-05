<?php

namespace App\Admin\Controllers\CustomerReport;

use App\Admin\Services\UserService;
use App\Models\ArReport\Unit;
use App\Models\ReportWarehouse\ReportWarehousePortal;
use App\Models\TransportCustomerReport;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TcrTopController extends AdminController
{
    protected $userService;
    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Sản lượng vận chuyển - Top 10-20-50';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->where('id', -1)->orderBy('id', 'desc');

        $grid->expandFilter();

        $year = date('Y');
        $month = date('m');
        $begin = $year."-".$month."-01";
        $finish = $year."-".$month."-".cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $grid->filter(function($filter) use ($begin, $finish) {
            $filter->disableIdFilter();
            $filter->column(4, function ($filter) {

                $filter->where(function ($query) {
                
                }, 'MKH', 'customer_id')->select($this->userService->GetListCustomer());
                
            });
            $filter->column(4, function ($filter) {

                $filter->where(function ($query) {
                
                }, 'Loại', 'type')->select(
                    [
                        'kg'    =>  "KG",
                        'm3'    =>  "M3",
                        'amount'    =>  "Doanh thu",
                    ]
                )->default('amount');
            });
            $filter->column(4, function ($filter) {

                $filter->where(function ($query) {
                
                }, 'Top', 'top')->select([
                    '10'    =>  "Top 10",
                    '20'    =>  "Top 20",
                    '50'    =>  "Top 50",
                ])->default(10);
            });

            $filter->column(6, function ($filter) use ($begin) {

                $filter->where(function ($query) {
                
                }, 'Ngày bắt đầu', 'begin')->date()->default($begin);
            });
            $filter->column(6, function ($filter) use ($finish) {

                $filter->where(function ($query) {
                
                }, 'Ngày kết thúc', 'finish')->date()->default($finish);
            });
        });

        $grid->header(function () use ($begin, $finish) {
            $params = $_GET;

            $top = 10;
            $type = "amount";

            if (isset($params['type'])) {
                $type = $params['type'];
            }

            if (isset($params['begin'])) {
                $begin = $params['begin'];
            }

            if (isset($params['finish'])) {
                $finish = $params['finish'];
            }

            if (isset($params['top'])) {
                $top = $params['top'];
            }

            $data = User::selectRaw(
                "admin_users.*, admin_users.symbol_name, count(*) as count, sum(payment_orders.amount) as amount, sum(payment_orders.total_kg) as kg,
                sum(payment_orders.total_m3) as m3, sum(payment_orders.total_advance_drag) as advance_drag")
            ->join('payment_orders', 'payment_orders.payment_customer_id', 'admin_users.id')
            ->where("payment_orders.created_at", ">=", $begin." 00:00:01")
            ->where("payment_orders.created_at", "<=", $finish." 23:59:59")
            ->where('payment_orders.status', 'payment_export')
            ->groupBy("admin_users.id")
            ->orderBy($type, "desc")
            ->limit($top)
            ->get()
            ->pluck('symbol_name', $type);

            
            $value = array_keys($data->toArray());

            foreach ($data->toArray() as $key => $row) {
                $names[] = $row . " -- " . number_format($key);
            }
            
            // color
            for ($i = 0; $i < $top; $i++) {
                $color[] = $this->rand_color();
            }

            $set = [
                'kg'    =>  "KG",
                'm3'    =>  "M3",
                'amount'    =>  "Doanh thu",
            ];
            $title = $set[$type];

            return view('admin.system.chart_transport_customer_report', compact('value', 'names', 'color', 'title'));
        });

        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->disableActions();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Unit);

        $form->text('title', "Tiêu đề")->rules(['required']);

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }

    public function rand_color() {
        return 'rgba(54, 162, 235, 0.2)';
    }
}
