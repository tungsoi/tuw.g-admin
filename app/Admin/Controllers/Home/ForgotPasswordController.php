<?php

namespace App\Admin\Controllers\Home;

use Encore\Admin\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\User;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends AdminController
{
    public function getForgotPassword(Request $request)
    {
        return view('admin.forgot-password');
    }

    public function postForgotPassword(Request $request)
    {
        $this->forgotPasswordValidator($request->all())->validate();

        $flag = User::whereUsername($request->username)->first();

        if (is_null($flag))
        {
            $errors = new MessageBag();
            $errors->add('username', 'Email không tồn tại trong hệ thống.');

            return view('admin.forgot-password')->withErrors($errors);
        }

        $email = $request->username;
        $token = $request->_token;

        DB::table('password_resets')->where('email', $email)->delete();
        DB::table('password_resets')->insert([
            'email' =>  $email,
            'token' =>  $token
        ]);

        $link = route('home.getVerifyForgotPassword')."?email=".$email."&token=".$token;

        Mail::send("admin.mail.forgot-password", ['link'  =>  $link], function($message) use ($email) {
            $message->to($email)
            ->subject("Thay đổi mật khẩu");
        });

        session()->flash('verify-forgot-password', 'Vui lòng kiểm tra Email để xác thực thao tác đổi mật khẩu của bạn.');

        return view('admin.login');
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function forgotPasswordValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required|email'
        ],[
            'username.required' =>  'Vui lòng nhập địa chỉ Email.',
            'username.email'    =>  'Vui lòng nhập đúng định dạng Email (VD: nguyenvantuan@gmail.com).'
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

    public function getVerifyForgotPassword()
    {
        $email = $_GET['email'];
        $token = $_GET['token'];

        return view('admin.verify-forgot-password', compact('email', 'token'));
    }

    public function postVerifyForgotPassword(Request $request)
    {
        $this->verifyForgotPasswordValidator($request->all())->validate();

        $password = Hash::make($request->password);
        $user = User::whereUsername($request->email)->first();

        if (! is_null($user))
        {
            $user->password = $password;
            $user->save();

            session()->flash('verify-forgot-password', 'Đổi mật khẩu thành công.');

            return view('admin.login');
        }
        else {
            $errors = new MessageBag();
            $errors->add('username', 'Email không tồn tại trong hệ thống.');

            return redirect()->back()->withErrors($errors);
        }
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function verifyForgotPasswordValidator(array $data)
    {
        return Validator::make($data, [
            'password'   => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6'
        ],[
            'password.min' =>  'Mật khẩu tối thiểu 6 ký tự.',
            'password_confirmation.min' =>  'Mật khẩu tối thiểu 6 ký tự.',
            'password.required_with'    =>  'Mật khẩu xác nhận không đúng.',
            'password.same'    =>  'Mật khẩu xác nhận không đúng.',
        ]);
    }
}
