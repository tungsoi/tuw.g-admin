<?php

namespace App\Admin\Controllers\CustomerReport;

use App\Admin\Actions\Export\TransportCustomerReportExporter;
use App\Admin\Services\UserService;
use App\Models\PurchaseCustomerReport;
use App\Models\PurchaseCustomerReportDetail;
use App\Models\System\Alert;
use App\Models\TransportCustomerReport;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
Use Encore\Admin\Widgets\Table;

class PurchaseCustomerReportController extends AdminController
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
    protected $title = 'Thống kê sản lượng Order khách hàng';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PurchaseCustomerReport());
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
                $reports = PurchaseCustomerReportDetail::whereUserId($customer->id)->orderBy('report_id', 'desc')->get();
                $temp = [];

                foreach ($reports as $report) {

                    $temp[] = [
                        'symbol_name'   =>  $symbol_name,
                        'title' =>  $report->mainReport->title,
                        'count' =>  $report->count,
                        'total_price_items'    =>  number_format($report->total_price_items),
                        'total_service'    =>  number_format($report->total_service),
                        'total_ship'    =>  number_format($report->total_ship),
                        'total_amount'    =>  number_format($report->total_amount)
                    ];
                }

                return view('admin.system.search_purchase_customer_report', compact('temp'));
            }
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');

        $grid->column('title', 'Tiêu đề');
        $grid->column('begin', 'Ngày bắt đầu');
        $grid->column('finish', 'Ngày kết thúc');
        $grid->column('count', 'SỐ LƯỢNG ĐƠN')->display(function () {
            return number_format($this->details->sum('count'));
        });
        $grid->column('total_price_items', 'TỔNG TIỀN SẢN PHẨM (VND)')->display(function () {
            return number_format($this->details->sum('total_price_items'));
        });
        $grid->column('total_service', 'TỔNG PHÍ DỊCH VỤ (VND)')->display(function () {
            return number_format($this->details->sum('total_service'));
        });
        $grid->column('total_ship', 'TỔNG TIỀN VẬN CHUYỂN NỘI ĐỊA (VND)')->display(function () {
            return number_format($this->details->sum('total_ship'));
        });
        $grid->column('total_amount', 'TỔNG GIÁ CUỐI (VND)')->display(function () {
            return number_format($this->details->sum('total_amount'));
        });
        $grid->disableBatchActions();
        $grid->paginate(10);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });

        Admin::script($this->scriptGrid());

        return $grid;
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

        $grid = new Grid(new PurchaseCustomerReportDetail());
        $grid->model()->whereReportId($id)->orderBy('total_amount', 'desc');

        if (isset($_GET['id'])) {
            $grid->model()->where('admin_users.id', $_GET['id']);
        }

        $grid->header(function ($query) use ($id) {
            $report = PurchaseCustomerReport::find($id);

            return view('admin.system.detail_purchase_customer_report', compact('report'));
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

        $grid->user()->symbol_name('Mã khách hàng')
        ->expand(function ($res) {
            $model = $this->user;
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
        $grid->column('total_price_items', 'Tổng tiền sản phẩm (VND)')->display(function ($data) {
            return number_format($data);
        })->totalRow(function ($amount) {
            return number_format($amount);
        });
        $grid->column('total_service', 'Tổng phí dịch vụ (VND)')->display(function ($data) {
            return number_format($data);
        })->totalRow(function ($amount) {
            return number_format($amount);
        });
        $grid->column('total_ship', 'Tổng tiền vận chuyển nội địa (VND)')->display(function ($data) {
            return number_format($data);
        })->totalRow(function ($amount) {
            return number_format($amount);
        });
        $grid->column('total_amount', 'Tổng giá cuối (VND)')->display(function ($data) {
            return number_format($data);
        })->totalRow(function ($amount) {
            return number_format($amount);
        });

        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->paginate(20);
        $grid->disableCreateButton();
        $grid->disableColumnSelector();

        Admin::script($this->scriptGrid());

        return $grid;
    }

    public function scriptGrid() {
        return <<<SCRIPT
        $('tfoot').each(function () {
            $(this).insertAfter($(this).siblings('thead'));
        });
SCRIPT;
    }
}
