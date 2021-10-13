<?php

namespace App\Admin\Services;

use App\Models\Setting\RoleUser;
use App\Models\System\Bank;
use App\Models\System\CustomerPercentService;
use App\Models\System\District;
use App\Models\System\Province;
use App\Models\System\Transaction;
use App\Models\System\Warehouse;
use App\User;
use Illuminate\Support\Str;

class UserService {

    const SALE_EMPLOYEE_ROLE = 3;
    const AR_EMPLOYEE_ROLE = 5;
    const ORDER_EMPLOYEE_ROLE = 4;
    const WAREHOUSE_EMPLOYEE_ROLE = 11;

    public function GetListSaleEmployee()
    {
        # code...
        $userIdsSaleRole = RoleUser::whereRoleId(self::SALE_EMPLOYEE_ROLE)->pluck('user_id');
        $users = User::whereIn('id', $userIdsSaleRole)
                        ->whereIsCustomer(User::ADMIN)
                        ->whereIsActive(User::ACTIVE)
                        ->orderBy('id', 'desc')
                        ->pluck('name', 'id');

        // if ($users->count() > 0) {
        //     foreach ($users as $key => $user) {
        //         $users[$key] = Str::upper($user);
        //     }
        // }

        return $users;
    }

    public function GetListPercentService() {
        return CustomerPercentService::orderBy('percent')->pluck('name', 'id');
    }

    public function GetListWarehouse() {
        return Warehouse::pluck('name', 'id');
    }

    public function GetListCustomer() {
        return User::whereIsCustomer(User::CUSTOMER)->orderBy('id', 'desc')->pluck('symbol_name', 'id');
    }

    public function GetListArEmployee() {
        $userIdsArRole = RoleUser::whereRoleId(self::AR_EMPLOYEE_ROLE)->pluck('user_id');
        $userIdsArRole[] = 1;
        $users = User::whereIn('id', $userIdsArRole)
                        ->whereIsCustomer(User::ADMIN)
                        ->whereIsActive(User::ACTIVE)
                        ->orderBy('id', 'desc')
                        ->pluck('name', 'id');

        // if ($users->count() > 0) {
        //     foreach ($users as $key => $user) {
        //         $users[$key] = Str::upper($user);
        //     }
        // }

        return $users;
    }

    public function isFilter() {
        if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
            return true;
        }

        return false;
    }

    public function GetCustomerTransactionHistory($customerId, $isHtml = true)
    {
        # code...
        $res = Transaction::where('money', ">", 0)
        ->where('customer_id', $customerId)
        ->orderBy('created_at', 'desc')
        ->get();

        $raw = [
            'order' =>  '',
            'payment_date'  =>  '',
            'order_link'    =>  '',
            'type_recharge' =>  '',
            'content'   =>  '',
            'before_payment'    =>  '',
            'down'   =>  '',
            'up'    =>  '',
            'after_payment',
            'user_id_created',
            'updated_user_id',
            'updated_at',
            'payment_order' => '',
            'payment_order_id'  =>  '',
            'bank'  =>  ''
        ];
        $data = [];

        foreach ($res as $key => $record) {
            $type = $record->type_recharge;
            if (in_array($type, [0, 1, 2])) {
                $up = $record->money;
                $down = null;
                $flag = 'up';
            } else {
                $down = $record->money;
                $up = null;
                $flag = 'down';
            }
            
            // try {
                $data[] = [
                    'id'    =>  $record->id,
                    'order' =>  $key + 1,
                    'payment_date'  =>  date('H:i | d-m-Y', strtotime($record->created_at)),
                    'order_link'    =>  null,
                    'type_recharge' =>  $record->type->name ?? "",
                    'content'   =>  $record->content,
                    'before_payment'    =>  '',
                    'down'   =>  $down,
                    'up'    => $up,
                    'after_payment' =>  '',
                    'flag'  =>  $flag,
                    'user_id_created'   =>  $record->userCreated->name ?? "",
                    'updated_user_id'   =>  $record->userUpdated->name ?? "",
                    'updated_at'    =>  $record->updated_at,
                    'payment_order' =>  $record->paymentOrder->order_number ?? "",
                    'payment_order_id'  =>  $record->paymentOrder->id ?? "",
                    'bank'  =>  $record->bank->bank_name ?? ""
                ];
            // } catch (\Exception $e) {
            //     dd($record);
            // }
            
        }

        $data = array_reverse($data);
        foreach ($data as $key => $raw) {
            if ($key == 0) {
                $data[0]['before_payment']  = 0;
                if ($data[0]['flag'] == 'up') {
                    $data[0]['after_payment'] = $data[0]['before_payment'] + $data[0]['up'];
                }
                else {
                    $data[0]['after_payment'] = $data[0]['before_payment'] - $data[0]['down'];
                }
            }
            else {
                $data[$key]['before_payment']  = $data[$key-1]['after_payment'];
                if ($data[$key]['flag'] == 'up') {
                    $data[$key]['after_payment'] = $data[$key]['before_payment'] + $data[$key]['up'];
                }
                else {
                    $data[$key]['after_payment'] = $data[$key]['before_payment'] - $data[$key]['down'];
                }
            }
        }

        $data = array_reverse($data);

        foreach ($data as $key => $last_raw) {

            if ($isHtml) {
                $data[$key]['before_payment'] = $data[$key]['before_payment'] >= 0 
                ? "<span style='color: green'>".number_format($data[$key]['before_payment'])."</span>"
                : "<span style='color: red'>".number_format($data[$key]['before_payment'])."</span>";
    
                $data[$key]['down'] = $data[$key]['down'] != null
                ? "<span style='color: red'>".number_format($data[$key]['down'])."</span>"
                : null;
                
                $data[$key]['up'] = $data[$key]['up'] != null
                ? "<span style='color: green'>".number_format($data[$key]['up'])."</span>"
                : null;
            
                $data[$key]['after_payment'] = $data[$key]['after_payment'] >= 0 
                ? "<span style='color: green'>".number_format($data[$key]['after_payment'])."</span>"
                : "<span style='color: red'>".number_format($data[$key]['after_payment'])."</span>";
            }

            unset($data[$key]['flag']);
        }

        return $data;
    }

    public function GetListProvince() {
        return Province::all()->pluck('name', 'province_id');
    }

    public function GetListDistrict() {
        return District::all()->pluck('name', 'district_id');
    }

    public function GetListOrderEmployee() {
        $userIdsSaleRole = RoleUser::whereRoleId(self::ORDER_EMPLOYEE_ROLE)->pluck('user_id');
        $users = User::whereIn('id', $userIdsSaleRole)
                        ->whereIsCustomer(User::ADMIN)
                        ->whereIsActive(User::ACTIVE)
                        ->orderBy('id', 'desc')
                        ->pluck('name', 'id');

        // if ($users->count() > 0) {
        //     foreach ($users as $key => $user) {
        //         $users[$key] = Str::upper($user);
        //     }
        // }

        return $users;
    }

    public function GetAllEmployee() {
        return User::whereIsCustomer(User::ADMIN)
        ->whereIsActive(User::ACTIVE)
        ->orderBy('id', 'desc')
        ->pluck('name', 'id');
    }

    public function GetListWarehouseEmployee() {
        $userIdsSaleRole = RoleUser::whereRoleId(self::WAREHOUSE_EMPLOYEE_ROLE)->pluck('user_id');
        $users = User::whereIn('id', $userIdsSaleRole)
                        ->whereIsCustomer(User::ADMIN)
                        ->whereIsActive(User::ACTIVE)
                        ->orderBy('id', 'desc')
                        ->pluck('name', 'id');

        // if ($users->count() > 0) {
        //     foreach ($users as $key => $user) {
        //         $users[$key] = Str::upper($user);
        //     }
        // }

        return $users;
    }

    public function GetListBankAccount() {
        $banks = Bank::all();

        $temp = [];

        foreach ($banks as $bank) {
            $temp[$bank->id] = $bank->bank_name . " - " . $bank->account_number . " - " . $bank->card_holder;
        }

        return $temp;
    }
}