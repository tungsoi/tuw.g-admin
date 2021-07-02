<?php

namespace App\Admin\Controllers\System;

use App\Admin\Actions\Customer\PurchaseOrder;
use App\Admin\Actions\Customer\Recharge;
use App\Admin\Actions\Customer\Transaction;
use App\Admin\Actions\Customer\TransportOrder;
use App\Admin\Services\UserService;
use App\Models\System\Transaction as SystemTransaction;
use App\Models\System\TransactionType;
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
        $grid->model()->whereIsCustomer(User::CUSTOMER)->orderByRaw('CHAR_LENGTH(wallet) DESC');

        $grid->filter(function($filter) {
            $filter->disableIdFilter();

            $filter->column(1/2, function ($filter) {
                $filter->like('name', 'Họ và tên');
            });
            $filter->column(1/2, function ($filter) {
                $filter->like('username', 'Email');
            });
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });

        $grid->column('number', 'STT');
        $grid->id('Hồ sơ')->display(function (){
            return "Xem";
        })->expand(function ($model) {
            $info = [
                "ID"    =>  $model->id,
                "Mã khách hàng" =>  $model->symbol_name,
                "Địa chỉ Email" =>  $model->email,
                "Số điện thoại" =>  $model->phone_number,
                "Số dư ví"  =>  number_format($model->wallet) ?? 0,
                "Ví cân"    =>  $model->wallet_weight,
                "Ngày mở tài khoản" =>   date('H:i | d-m-Y', strtotime($this->created_at)),
                "Giao dịch gần nhất"    =>  null,
                "Kho nhận hàng" =>  $model->warehouse->name ?? "",
                "Địa chỉ"   =>  $model->address,
                "Quận / Huyện"  =>  $model->getDistrict(),
                "Tỉnh / Thành phố" => $model->getProvince()
            ];
        
            return new Table(['Thông tin', 'Nội dung'], $info);
        })->width(70);
        $grid->symbol_name('Mã khách hàng')->style('text-align: center;')->editable();
        $grid->ware_house_id('Kho nhận hàng')->style('text-align: center;')->editable('select', $this->userService->GetListWarehouse());
        $grid->wallet('Ví tiền')->display(function () {
            $label = $this->wallet < 0 ? "red" : "green";
            return "<span style='color: {$label}'>".number_format($this->wallet)."</span>";
        })->style('text-align: right;');
        $grid->wallet_weight('Ví cân');

        $states = [
            'on'  => ['value' => User::ACTIVE, 'text' => 'Mở', 'color' => 'success'],
            'off' => ['value' => User::DEACTIVE, 'text' => 'Khoá', 'color' => 'danger'],
        ];
        $grid->staff_sale_id('Sale')->editable('select', $this->userService->GetListSaleEmployee());
        $grid->customer_percent_service('Phí dịch vụ')->editable('select', $this->userService->GetListPercentService());
        $grid->note('Ghi chú')->editable()->width(100);
        $grid->column('is_active', 'Trạng thái')->switch($states)->style('text-align: center');
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(20);
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();    
            $actions->append(new PurchaseOrder($actions->getKey()));
            $actions->append(new TransportOrder($actions->getKey()));
            $actions->append(new Recharge($actions->getKey()));
            $actions->append(new Transaction($actions->getKey()));
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
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new User());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->image('avatar', trans('admin.avatar'));
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', trans('admin.name'))->rules('required');
        $form->text('symbol_name', 'Mã khách hàng')
            ->creationRules(['required', 'unique:admin_users,symbol_name'])
            ->updateRules(['required', "unique:admin_users,symbol_name,{{id}}"]);

        $form->divider();
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        $form->hidden('is_customer')->default(User::ADMIN);
        $states = [
            'off' => ['value' => User::DEACTIVE, 'text' => 'Đã nghỉ', 'color' => 'danger'],
            'on'  => ['value' => User::ACTIVE, 'text' => 'Làm việc', 'color' => 'success']
        ];
        $form->switch('is_active', 'Trạng thái')->states($states)->default(1);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
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
            if (isset($_GET['mode']) && $_GET['mode'] == 'recharge') {
                $mode = 'recharge';

                if (isset($_GET['transaction_id']) && $_GET['transaction_id'] != "") { 
                    $form = $this->formRecharge($id, $_GET['transaction_id'])->edit($_GET['transaction_id'])->render();
                } else {
                    $form = $this->formRecharge($id, "")->render();
                }
            }
            return view('admin.system.customer.transaction', compact('customer', 'empty', 'data', 'mode', 'form'))->render();

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
}
