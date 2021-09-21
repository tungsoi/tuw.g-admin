<?php

namespace App\Http\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;

class CustomerUpdateInformation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Admin::user() && $request->getPathInfo() != "/admin/auth/setting" && $request->getPathInfo() != "/admin/auth/logout") {
            $user = Admin::user();
            if (! $this->condition($user)) {

                admin_error('Thông báo', 'Vui lòng cập nhật đầy đủ thông tin của Bạn trước khi sử dụng hệ thống.');
                return redirect()->route('admin.setting');
            }
        }

        return $next($request);
    }

    public function condition($user) {
        if (
            is_null($user->phone_number) || $user->phone_number == ""
            || is_null($user->address) || $user->address == ""
            || is_null($user->ware_house_id) || $user->ware_house_id == ""
            || is_null($user->province) || $user->province == ""
            || is_null($user->district) || $user->district == ""
        ) {
            return false;
        }

        return true;
    }
}
