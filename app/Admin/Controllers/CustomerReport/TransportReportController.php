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

        // $grid->expandFilter();
        // $grid->filter(function($filter) {
        //     $filter->disableIdFilter();
        //     $filter->equal('id', 'Mã khách hàng')->select($this->userService->GetListCustomer());
        //     $filter->between('created_at', 'Thời gian')->date();
        // });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');

        $grid->column('title', 'Tiêu đề');
        $grid->column('begin', 'Ngày bắt đầu');
        $grid->column('finish', 'Ngày kết thúc');
        // $grid->symbol_name('Mã khách hàng');
        // $grid->wallet('Ví tiền')->display(function () {
        //     $label = $this->wallet < 0 ? "red" : "green";
        //     return "<span style='color: {$label}'>".number_format($this->wallet)."</span>";
        // })->style('text-align: right; max-width: 150px;');
        // $grid->column('transport_count', 'Số lượng đơn')->display(function () {
        //     return $this->paymentOrders->count();
        // });
        // $grid->column('amount', 'Tổng doanh thu (VND)')->display(function () {
        //     return number_format($this->paymentOrders->sum('amount'));
        // });
        // $grid->column('total_kg', 'Tổng cân (Kg)')->display(function () {
        //     return number_format($this->paymentOrders->sum('total_kg'));
        // });
        // $grid->column('total_m3', 'Tổng khối (M3)')->display(function () {
        //     return number_format($this->paymentOrders->sum('total_m3'));
        // });
        // $grid->column('total_advance_drag', 'Tổng ứng kéo (Tệ)')->display(function () {
        //     return number_format($this->paymentOrders->sum('total_advance_drag'));
        // });

        // $grid->disableActions();
        $grid->disableBatchActions();
        $grid->paginate(100);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Alert);

        $form->text('title', "Tiêu đề")->rules(['required']);
        $form->summernote('content', "Nội dung")->rules(['required']);
        $isDefault = [
            'off' => ['value' => 0, 'text' =>  'Tắt', 'color' => 'danger'],
            'on'  => ['value' => 1, 'text' => 'Mở', 'color' => 'success']
        ];
        $form->switch('status', 'Loại kho')->states($isDefault)->default(1);
        $form->hidden('created_user_id')->default(Admin::user()->id);
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
        ->groupBy("admin_users.id")
        ->orderBy("amount", "desc");

        $grid->header(function ($query) use ($report) {
            $data = User::selectRaw(
                "admin_users.*, admin_users.symbol_name, count(*) as count, sum(payment_orders.amount) as amount, sum(payment_orders.total_kg) as kg,
                sum(payment_orders.total_m3) as m3, sum(payment_orders.total_advance_drag) as advance_drag")
            ->join('payment_orders', 'payment_orders.payment_customer_id', 'admin_users.id')
            ->where("payment_orders.created_at", ">=", $report->begin)
            ->where("payment_orders.created_at", "<=", $report->finish)
            ->groupBy("admin_users.id")
            ->orderBy("amount", "desc")
            ->get();

            return view('admin.system.detail_transport_customer_report', compact('data', 'report'));
        });

        $grid->expandFilter();
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->equal('id', 'Mã khách hàng')->select($this->userService->GetListCustomer());
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
        $grid->wallet('Ví tiền')->display(function () {
            $label = $this->wallet < 0 ? "red" : "green";
            return "<span style='color: {$label}'>".number_format($this->wallet)."</span>";
        })->style('text-align: right; max-width: 150px;');

        $grid->column('count', 'Số lượng đơn');
        $grid->column('amount', 'Tổng doanh thu (VND)')->display(function () {
            return number_format($this->amount);
        })->sortable();
        $grid->column('kg', 'Tổng cân (Kg)')->display(function () {
            return number_format($this->kg);
        })->sortable();
        $grid->column('m3', 'Tổng khối (M3)')->display(function () {
            return number_format($this->m3);
        })->sortable();
        $grid->column('advance_drag', 'Tổng ứng kéo (Tệ)')->display(function () {
            return number_format($this->advance_drag);
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
