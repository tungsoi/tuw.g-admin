<?php

namespace App\Admin\Controllers\System;

use App\Admin\Actions\Customer\PurchaseOrder;
use App\Admin\Actions\Customer\Recharge;
use App\Admin\Actions\Customer\Transaction;
use App\Admin\Actions\Customer\TransportOrder;
use App\Admin\Actions\Customer\WalletWeight;
use App\Admin\Actions\Customer\HistoryWalletWeight;
use App\Admin\Services\UserService;
use App\Models\System\Transaction as SystemTransaction;
use App\Models\System\TransactionType;
use App\Models\System\TransactionWeight;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;
use Encore\Admin\Controllers\AdminController;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Str;
Use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Encore\Admin\Admin as AdminSystem;

class CustomerController extends AdminController
{
    protected $userService;
    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return 'Danh sách khách hàng';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->whereIsCustomer(User::CUSTOMER)->orderBy('id', 'desc');

        $grid->expandFilter();
        $grid->filter(function($filter) {
            $filter->disableIdFilter();

            $filter->column(1/4, function ($filter) {
                $filter->equal('id', 'Mã khách hàng')->select($this->userService->GetListCustomer());
                $filter->equal('staff_sale_id', 'Nhân viên kinh doanh')->select($this->userService->GetListSaleEmployee());
            });
            $filter->column(1/4, function ($filter) {
                $filter->like('name', 'Họ và tên');
                $filter->equal('staff_order_id', 'Nhân viên đặt hàng')->select($this->userService->GetListOrderEmployee());
            });
            $filter->column(1/4, function ($filter) {
                $filter->like('username', 'Email');
                $filter->equal('customer_percent_service', 'Phí dịch vụ')->select($this->userService->GetListPercentService());
            });
            $filter->column(1/4, function ($filter) {
                $filter->like('phone_number', 'Số điện thoại');
                $filter->equal('ware_house_id', 'Kho nhận hàng')->select($this->userService->GetListWarehouse());
            });

            Admin::style('
                #filter-box label {
                    padding: 0px !important;
                    padding-top: 10px;
                    font-weight: 600;
                    font-size: 12px;
                }
                #filter-box .col-sm-2 {
                    width: 100% !important;
                    text-align: left;
                    padding: 0px 15px 3px 15px !important;
                }
                #filter-box .col-sm-8 {
                    width: 100% !important;
                }
            ');
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });

        $grid->column('number', 'STT');
        $grid->id('Hồ sơ')->display(function (){
            return "Tất cả";
        })->expand(function ($model) {
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
                "Quận / Huyện"  =>  $model->getDistrict(),
                "Tỉnh / Thành phố" => $model->getProvince(),
                'Nhân viên kinh doanh'  =>  $model->saleEmployee->name ?? "",
                'Nhân viên đặt hàng'    =>  $model->orderEmployee->name ?? "",
                'Phí dịch vụ'           =>  $model->percentService->name ?? "",
                'Giá cân thanh toán'    =>  $model->default_price_kg,
                'Giá khối thanh toán'   =>  $model->default_price_m3
            ];
        
            return new Table(['Thông tin', 'Nội dung'], $info);
        })->style('max-width: 150px; text-align: center;');

        $grid->symbol_name('Mã khách hàng')->style('max-width: 150px;');
        $grid->wallet('Ví tiền')->display(function () {
            $label = $this->wallet < 0 ? "red" : "green";
            return "<span style='color: {$label}'>".number_format($this->wallet)."</span>";
        })->style('text-align: right; max-width: 150px;');
        $grid->wallet_weight('Ví cân')->style('text-align: right; max-width: 150px;');

        $states = [
            'on'  => ['value' => User::ACTIVE, 'text' => 'Mở', 'color' => 'success'],
            'off' => ['value' => User::DEACTIVE, 'text' => 'Khoá', 'color' => 'danger'],
        ];
        $grid->staff_sale_id('NV Sale')->editable('select', $this->userService->GetListSaleEmployee())->style('max-width: 150px;');
        $grid->staff_order_id('NV Order')->editable('select', $this->userService->GetListOrderEmployee())->style('max-width: 150px;');
        $grid->customer_percent_service('Phí dịch vụ')->editable('select', $this->userService->GetListPercentService())->style('max-width: 150px;');
        $grid->default_price_kg('Giá cân')->editable()->style('max-width: 150px;');
        $grid->default_price_m3('Giá khối')->editable()->style('max-width: 150px;');
        $grid->ware_house_id('Kho nhận hàng')->style('text-align: center; width: 200px;')->editable('select', $this->userService->GetListWarehouse());
        $grid->note('Ghi chú')->editable()->style('max-width: 150px;');
        $grid->column('is_active', 'Trạng thái')->switch($states)->style('text-align: center');


        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(20);
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();    
            $actions->append(new PurchaseOrder($actions->getKey()));
            // $actions->append(new TransportOrder($actions->getKey()));
            $actions->append(new Recharge($actions->getKey()));
            $actions->append(new Transaction($actions->getKey()));
            $actions->append(new WalletWeight($actions->getKey()));
            $actions->append(new HistoryWalletWeight($actions->getKey()));
        });
        
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return redirect()->route('admin.customers.index');
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());
        $form->setTitle('Cập nhật thông tin');

        $service = new UserService();
        $form->column(1/2, function ($form) use ($service) {
        
            $form->display('username', 'Tên đăng nhập');
            $form->display('symbol_name', 'Mã khách hàng');
            $form->text('name', 'Họ và tên')->rules('required');
            $form->text('phone_number', 'Số điện thoại')->rules('required');

            $form->divider();
            $form->select('staff_sale_id', 'Nhân viên Kinh doanh')->options($service->GetListSaleEmployee())->rules('required');
            $form->select('staff_order_id', 'Nhân viên Đặt hàng')->options($service->GetListOrderEmployee());
            $form->select('customer_percent_service', '% Phí dịch vụ')->options($service->GetListPercentService())->rules('required');
        });
        $form->column(1/2, function ($form) use ($service) {
            $form->select('ware_house_id', 'Kho hàng')->options($service->GetListWarehouse())->rules('required');
            $form->select('province', 'Tỉnh / Thành phố')->options($service->GetListProvince())->rules('required');
            $form->select('district', 'Quận / Huyện')->options($service->GetListDistrict())->rules('required');
            $form->text('address', 'Địa chỉ')->rules('required');

            $form->divider();
            $form->password('password', trans('admin.password'))->rules('confirmed|required');
            $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
                ->default(function ($form) {
                    return $form->model()->password;
                });
        });

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function transaction($id, Content $content)
    {
        return $content
            ->title($this->title() . " / LỊCH SỬ GIAO DỊCH")
            ->body($this->transactionGrid($id));
    }

    public function transactionGrid($id) {

        $grid = new Grid(new SystemTransaction());
        $grid->model()->whereCustomerId(0)->where('money', '!=', 0)->orderBy('id', 'desc');

        $grid->header(function () use ($id) {

            $customer = User::select('id', 'symbol_name', 'wallet')->whereId($id)->first();
            $empty = true;
            $service = new UserService();
            $data = $service->GetCustomerTransactionHistory($id);

            $mode = "";
            $form = "";
            $transactionId = "";
            if (isset($_GET['mode']) && $_GET['mode'] == 'recharge') {
                $mode = 'recharge';

                if (isset($_GET['transaction_id']) && $_GET['transaction_id'] != "") { 
                    $transactionId = $_GET['transaction_id'];
                    $flag = SystemTransaction::find($transactionId);

                    if ($flag) {
                        $form = $this->formRecharge($id, $transactionId)->edit($transactionId)->render();
                    } else {
                        $form = $this->formRecharge($id, "")->render();
                    }
                } else {
                    $form = $this->formRecharge($id, "")->render();
                }
            }
            return view('admin.system.customer.transaction', compact('customer', 'empty', 'data', 'mode', 'form', 'transactionId'))->render();

        });
        
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });
        $grid->paginate(1000);
        $grid->disableColumnSelector();
        $grid->disablePagination();

        return $grid;
    }

    public function formRecharge($id, $recordId)
    {
        # code...
        $form = new Form(new SystemTransaction);

        if ($recordId == "") {
            $form->setTitle('Tạo giao dịch');
            $route = route('admin.customers.storeRecharge');
        } else {
            $form->setTitle('Chỉnh sửa giao dịch');
            $route = route('admin.customers.updateRecharge');
            AdminSystem::script($this->script());
        }

        $form->setAction($route);

        $form->select('type_recharge', 'Loại giao dịch')->options(TransactionType::pluck('name', 'id'))->default(0)->rules('required');
        $form->currency('money', 'Số tiền cần nạp')->rules('required|min:4')->symbol('VND')->digits(0)->width(100);
        $form->text('content', 'Nội dung')->placeholder('Ghi rõ nội dung giao dịch');
        $form->hidden('user_id_created')->default(Admin::user()->id);
        $form->hidden('customer_id')->default($id);
        $form->hidden('record_id')->default($recordId);
        $form->hidden('updated_user_id')->default(Admin::user()->id);

        $form->confirm('Xác nhận thực hiện giao dịch ?');

        $form->tools(function (Form\Tools $tools) use ($id, $recordId) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
            
            if ($recordId != "") {
                $tools->append(new Recharge($id, "Tạo giao dịch nạp tiền"));
            }
        });

        return $form;
    }

    public function storeRecharge(Request $request)
    {
        # code...
        $data = $request->all();
        unset($data['updated_user_id']);

        SystemTransaction::create($data);
        User::find($request->customer_id)->updateWalletByHistory();

        admin_toastr("Tạo giao dịch thành công", 'success');

        return back();
    }

    public function updateRecharge(Request $request)
    {
        # code...
        $data = $request->all();
        unset($data['user_id_created']);

        SystemTransaction::find($request->record_id)->update($data);
        User::find($request->customer_id)->updateWalletByHistory();

        admin_toastr("Chỉnh sửa giao dịch thành công", 'success');

        return back();
    }

    public function script()
    {
        # code...
        return <<<SCRIPT
            $("input[name='_method']").val('POST');
        SCRIPT;
    }

    public function walletWeight($id, Content $content) {
        return $content
            ->title($this->title() . " / LỊCH SỬ VÍ CÂN")
            ->body($this->walletWeightGrid($id));
    }

    public function walletWeightGrid($id) {

        $grid = new Grid(new SystemTransaction());
        $grid->model()->whereCustomerId(0)->where('money', '!=', 0)->orderBy('id', 'desc');

        $grid->header(function () use ($id) {

            $customer = User::select('id', 'symbol_name', 'wallet_weight')->whereId($id)->first();
            $empty = true;
            $service = new UserService();
            $data = TransactionWeight::whereCustomerId($id)->get();

            $mode = "";
            $form = "";
            $transactionId = "";
            if (isset($_GET['mode']) && $_GET['mode'] == 'recharge') {
                $mode = $_GET['mode'];

                $form = $this->formRechargeWeight($id, "")->render();
            }
            return view('admin.system.customer.transactionWalletWeight', compact('customer', 'empty', 'data', 'mode', 'form', 'transactionId'))->render();

        });
        
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });
        $grid->paginate(1000);
        $grid->disableColumnSelector();
        $grid->disablePagination();

        return $grid;
    }

    public function formRechargeWeight($id, $recordId)
    {
        # code...
        $form = new Form(new TransactionWeight());

        if ($recordId == "") {
            $form->setTitle('Tạo giao dịch ví cân');
            $route = route('admin.customers.storeRechargeWeight');
        } else {
            $form->setTitle('Chỉnh sửa giao dịch ví cân');
            $route = route('admin.customers.updateRechargeWeight');
            AdminSystem::script($this->script());
        }

        $form->setAction($route);

        $form->html('Người thực hiện: ' . Admin::user()->name);
        $form->html('Số dư ví cân: ' . number_format(Admin::user()->wallet_weight));
        $form->hidden('user_id_created', 'Người tạo')->default(Admin::user()->id);
        $form->currency('kg', 'Số cân')->rules('required|min:4')->symbol('KG')->digits(0)->width(100);
        $form->text('content', 'Nội dung')->placeholder('Ghi rõ nội dung giao dịch')->rules('required|min:4');
        $form->hidden('user_id_created')->default(Admin::user()->id);
        $form->hidden('customer_id')->default($id);
        $form->hidden('record_id')->default($recordId);
        $form->hidden('updated_user_id')->default(Admin::user()->id);

        $form->confirm('Xác nhận thực hiện giao dịch ?');

        $form->tools(function (Form\Tools $tools) use ($id, $recordId) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        return $form;
    }

    public function storeRechargeWeight(Request $request) {
        if (Admin::user()->wallet_weight < $request->kg) {
            admin_error('Số cân trong ví của bạn không đủ để chuyển cho Khách hàng này.');
            return back();
        } else {
            $user = Admin::user();
            $user->wallet_weight -= $request->kg;
            $user->save();
    
            TransactionWeight::create($request->all());
            $customer = User::find($request->customer_id);
            $customer->wallet_weight += $request->kg;
            $customer->save();
    
            admin_toastr('Nạp ví cân thành công', 'success');
    
            return back();
        }
    }
}
