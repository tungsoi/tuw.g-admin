<?php

namespace App\Admin\Controllers\CustomerReport;

use App\Admin\Actions\Export\TransportCustomerReportExporter;
use App\Admin\Services\UserService;
use App\Models\System\Alert;
use App\Models\TransportCustomerReport;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
Use Encore\Admin\Widgets\Table;

class TransportReportController extends AdminController
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
    protected $title = 'Thống kê sản lượng vận chuyển khách hàng';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TransportCustomerReport());
        $grid->model()->orderBy('id', 'desc');
        // $grid = new Grid(new User());
        // $grid->model()->whereIsCustomer(User::CUSTOMER)->with('paymentOrders');

        // if (isset($_GET['type_wallet']) && $_GET['type_wallet'] == 0) {
        //     $grid->model()->orderByRaw('CONVERT(wallet, SIGNED) desc');
        // } else {
        //     $grid->model()->orderByRaw('CONVERT(wallet, SIGNED) asc');
        // }

        $grid->expandFilter();
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->where(function ($query) {
                
            }, 'Mã khách hàng', 'customer_id')->select($this->userService->GetListCustomer());
        });

        $grid->header(function () {
            if (isset($_GET['customer_id'])) {
                $customer = User::find($_GET['customer_id']);
                $symbol_name = $customer->symbol_name;
                $reports = TransportCustomerReport::orderBy('id', 'desc')->get();
                $temp = [];

                foreach ($reports as $report) {
                    $data = User::selectRaw(
                        "admin_users.*, admin_users.symbol_name, count(*) as count, sum(payment_orders.amount) as amount, sum(payment_orders.total_kg) as kg,
                        sum(payment_orders.total_m3) as m3, sum(payment_orders.total_advance_drag) as advance_drag")
                    ->join('payment_orders', 'payment_orders.payment_customer_id', 'admin_users.id')
                    ->where("payment_orders.created_at", ">=", $report->begin)
                    ->where("payment_orders.created_at", "<=", $report->finish)
                    ->where('payment_orders.status', 'payment_export')
                    ->groupBy("admin_users.id")
                    ->orderBy("amount", "desc")
                    ->where('admin_users.id', $customer->id)
                    ->get();

                    $temp[] = [
                        'symbol_name'   =>  $symbol_name,
                        'title' =>  $report->title,
                        'count' =>  $data->sum('count'),
                        'kg'    =>  number_format($data->sum('kg'), 1),
                        'm3'    =>  number_format($data->sum('m3'), 3),
                        'amount'    =>  number_format($data->sum('amount') - $data->sum('advance_drag'))
                    ];
                }

                return view('admin.system.search_transport_customer_report', compact('temp'));
            }
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');

        $grid->column('title', 'Tiêu đề');
        $grid->column('begin', 'Ngày bắt đầu');
        $grid->column('finish', 'Ngày kết thúc');
        $grid->column('total_kg', 'Tổng cân (Kg)')->display(function () {
            return $this->total()['kg'];
        });
        $grid->column('total_m3', 'Tổng khối (M3)')->display(function () {
            return $this->total()['m3'];
        });
        $grid->column('amount', 'Tổng doanh thu (VND)')->display(function () {
            return $this->total()['amount'];
        });
        $grid->column('kg', 'KG Trung Quốc')->editable();
        $grid->disableBatchActions();
        $grid->paginate(10);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });

        Admin::script($this->scriptGrid());

        return $grid;
    }

    public function scriptGrid() {
        return <<<SCRIPT
        console.log('grid js');
        
 
SCRIPT;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TransportCustomerReport());

        $form->text('kg', "KG nhận bên Trung quốc");
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }

    public function detail($id) {
        $report = TransportCustomerReport::find($id);
        $begin = $report->begin;
        $finish = $report->finish;

        $grid = new Grid(new User());
        $grid->model()->selectRaw(
            "admin_users.*, admin_users.symbol_name, count(*) as count, sum(payment_orders.amount) as amount, sum(payment_orders.total_kg) as kg,
            sum(payment_orders.total_m3) as m3, sum(payment_orders.total_advance_drag) as advance_drag")
        ->join('payment_orders', 'payment_orders.payment_customer_id', 'admin_users.id')
        ->where("payment_orders.created_at", ">=", $begin)
        ->where("payment_orders.created_at", "<=", $finish)
        ->where('payment_orders.status', 'payment_export')
        ->groupBy("admin_users.id")
        ->orderBy("amount", "desc");

        if (isset($_GET['id'])) {
            $grid->model()->where('admin_users.id', $_GET['id']);
        }

        $grid->header(function ($query) use ($report) {
            $data = User::selectRaw(
                "admin_users.*, admin_users.symbol_name, count(*) as count, sum(payment_orders.amount) as amount, sum(payment_orders.total_kg) as kg,
                sum(payment_orders.total_m3) as m3, sum(payment_orders.total_advance_drag) as advance_drag")
            ->join('payment_orders', 'payment_orders.payment_customer_id', 'admin_users.id')
            ->where("payment_orders.created_at", ">=", $report->begin)
            ->where("payment_orders.created_at", "<=", $report->finish)
            ->where('payment_orders.status', 'payment_export')
            ->groupBy("admin_users.id")
            ->orderBy("amount", "desc")
            ->get();

            return view('admin.system.detail_transport_customer_report', compact('data', 'report'));
        });

        $grid->expandFilter();
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->where(function ($query) {
                
            }, 'Mã khách hàng', 'id')->select($this->userService->GetListCustomer());
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');

        $grid->symbol_name('Mã khách hàng')
        ->expand(function ($model) {
            $info = [
                "ID"    =>  $model->id,
                "Mã khách hàng" =>  $model->symbol_name,
                "Địa chỉ Email" =>  $model->email,
                "Số điện thoại" =>  $model->phone_number,
                "Ví tiền"  =>  number_format($model->wallet) ?? 0,
                "Ví cân"    =>  $model->wallet_weight . " (kg)",
                "Ngày mở tài khoản" =>   date('H:i | d-m-Y', strtotime($this->created_at)),
                "Giao dịch gần nhất"    =>  null,
                "Kho nhận hàng" =>  ($model->warehouse->name ?? "" ) . " - " . ( $model->warehouse->address ?? ""),
                "Địa chỉ"   =>  $model->address,
                "Quận / Huyện"  =>  $model->district != "" ? ($model->districtLink->type . '-' . $model->districtLink->name) : "",
                "Tỉnh / Thành phố" => $model->province != "" ? ($model->provinceLink->type . '-' . $model->provinceLink->name) : "",
                'Nhân viên kinh doanh'  =>  $model->saleEmployee->name ?? "",
                'Nhân viên đặt hàng'    =>  $model->orderEmployee->name ?? "",
                'Phí dịch vụ'           =>  $model->percentService->name ?? "",
                'Giá cân thanh toán'    =>  $model->default_price_kg,
                'Giá khối thanh toán'   =>  $model->default_price_m3,
            ];
        
            return new Table(['Thông tin', 'Nội dung'], $info);
        })->style('width: 100px; text-align: center;');

        $grid->column('count', 'Số lượng đơn');
        $grid->column('kg', 'Tổng cân (Kg)')->display(function () {
            return number_format($this->kg, 1);
        })->sortable();
        $grid->column('m3', 'Tổng khối (M3)')->display(function () {
            return number_format($this->m3, 3);
        })->sortable();
        $grid->column('amount', 'Tổng doanh thu (VND)')->display(function () {
            return number_format($this->amount - $this->advance_drag);
        })->sortable();

        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->paginate(20);
        $grid->disableCreateButton();
        $grid->disableColumnSelector();

        $grid->exporter(new TransportCustomerReportExporter($report->id));

        return $grid;
    }
}
