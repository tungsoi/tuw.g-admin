<?php

namespace App\Admin\Controllers;

use App\Admin\Services\UserService;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @var string
     */
    protected $loginView = 'admin::login';

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view($this->loginView);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $this->loginValidator($request->all())->validate();

        $credentials = [
            $this->username()   => $request->username,
            'password'          =>  $request->password,
            'is_active'            =>  User::ACTIVE
        ];
        $remember = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->disableView();
            }
        );

        return $content
            ->title(trans('admin.user_setting'))
            ->body($form->edit(Admin::user()->id));
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());
        $form->setTitle('Cập nhật thông tin');

        $service = new UserService();
        $form->column(1/2, function ($form) use ($service) {
            
            $form->display('username', 'Tên đăng nhập');

            if (Admin::user()->isRole('customer')) {
                $form->text('symbol_name', 'Mã khách hàng')
                ->creationRules(['required', "unique:admin_users"])
                ->updateRules(['required', "unique:admin_users,symbol_name,{{id}}"]);
            }
            $form->text('name', 'Họ và tên')->rules('required');
            $form->text('phone_number', 'Số điện thoại')->rules('required');

            if (Admin::user()->isRole('customer')) {
                $form->divider();
                $form->select('staff_sale_id', 'Nhân viên Kinh doanh')->options($service->GetListSaleEmployee());
                $form->select('staff_order_id', 'Nhân viên Đặt hàng')->options($service->GetListOrderEmployee());
                $form->select('customer_percent_service', '% Phí dịch vụ')->options($service->GetListPercentService())->readonly();
            }
       
            $form->image('avatar', trans('admin.avatar'));
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
            $form->divider();
            $form->html('Vui lòng cho chúng tôi biết bạn là khách hàng gì ?');

            if (! Admin::user()->isRole('customer')) {
                $form->select('type_customer', 'Loại khách hàng')->options([
                    '',
                    'Khách hàng Vận chuyển',
                    'Khách hàng Order',
                    'Cả 2'
                ])->rules('required');
            }
        });
       

        $form->setAction(admin_url('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect()->route('admin.home');
        });

        return $form;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }
}
