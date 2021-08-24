<?php

namespace App\Admin\Controllers\Home;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Encore\Admin\Facades\Admin;

class RegisterController extends Controller
{
    public function index()
    {
        return view('home.register');
    }

    public function register(Request $request) {

        $this->registerValidator($request->all())->validate();

        $user = User::create([
            'username'      =>  $request->username,
            'email'         =>  $request->username,
            'password'      =>  Hash::make('123456')
        ]);

        $user->symbol_name = 'MKH'.str_pad($user->id, 5, 0, STR_PAD_LEFT);
        $user->save();

        DB::table('admin_role_users')->insert([
            'role_id'   =>  2,
            'user_id'   =>  $user->id
        ]);

        $credentials = [
            'username'  =>  $request->username,
            'password'  =>  '123456'
        ];
        
        $remember = true;

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
    protected function registerValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required|email|unique:admin_users,username'
        ],[
            'username.required' =>  'Vui lòng nhập địa chỉ Email.',
            'username.email'    =>  'Vui lòng nhập đúng định dạng Email (VD: nguyenvantuan@gmail.com).',
            'username.unique'   =>  'Email này đã được đăng ký trên hệ thống, vui lòng sử dụng email khác.'
        ]);
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
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_success("Đăng ký tài khoản thành công", 'Chào mừng bạn đã trở thành khách hàng của ALILOGI.');

        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
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
}
