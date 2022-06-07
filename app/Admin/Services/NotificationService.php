<?php

namespace App\Admin\Services;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\UserNotification;
use App\User;

class NotificationService 
{
    const TITLE =  'Alilogi Thông Báo';
    const ICON = "https://img.icons8.com/doodle/2x/tow-truck--v1.png 2x";

    const DISPLAY = [
        'transport' =>  'transport_order_detail_screen',
        'purchase'  =>  'purchase_order_detail_screen',
        'transaction'   =>  'transaction_screen'
    ];

    protected $serverKey = 'AAAAmCs1ZTI:APA91bEhjfd5zbAn_5wQFt7sCKlclpAbDCQ4auPfyMpHP6md6t_BuRG8A20wGQVRw1bgXDs02amy0ByeXAUIZJsQ0SZHJmeaQfeAKr5JaF36YLpgXF9dOPsHAnwBaqLqqkDCIM3TmU8j';
    
    public function call($data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization:key=' . $this->serverKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        if ($result === FALSE) {
            curl_close($ch);
            return [
                'status'    =>  false,
                'msg'       =>  curl_error($ch)
            ];
        }        
        curl_close($ch);
        return [
            'status'    =>  true,
            'msg'       =>  'success'
        ];
    }

    public function buildBody($tokens, $title, $display, $id) {
        return [
            "registration_ids" => $tokens,
            "notification" => [
                "title"     =>  self::TITLE,
                "body"      =>  $title,
                "icon"      =>  self::ICON
            ],
            'data' => [
                "display"   =>  $display,
                "id"        =>  $id
            ]
        ];
    }

    public function getTitleTransportNotification($order_number) {
        return "Bạn có 1 đơn hàng mới chờ xuất kho - Mã đơn hàng ".$order_number.". Bạn đến lấy hàng sớm nhé !";
    }


    public function sendTransportOrder($user_id, $order_id) 
    {
        $order_number = PaymentOrder::find($order_id)->order_number;

        $result = $this->call(
            $this->buildBody(
                User::whereId($user_id)->pluck('device_key')->all(),
                $this->getTitleTransportNotification($order_number),
                self::DISPLAY['transport'],
                $order_id
            )
        );

        UserNotification::create([
            'user_id'   =>  $user_id,
            'type'  =>  'transport_order',
            'title' =>  $this->getTitleTransportNotification($order_number),
            'order_id' =>   $order_id,
            'step'  =>  $result['msg']
        ]);
    }

    public function sendPurchaseOrder($user_id, $order_id) {
        $order_number = PurchaseOrder::find($order_id)->order_number;
        $title = "Đơn hàng mua hộ ".$order_number. " đã đặt cọc thành công";

        $result = $this->call(
            $this->buildBody(
                User::whereId($user_id)->pluck('device_key')->all(),
                $title,
                self::DISPLAY['purchase'],
                $order_id
            )
        );

        UserNotification::create([
            'user_id'   =>  $user_id,
            'type'  =>  'purchase_order',
            'title' =>  $title,
            'order_id' =>   $order_id,
            'step'  =>  $result['msg']
        ]);
    }

    public function sendTransaction($user_id, $title) {

        $result = $this->call(
            $this->buildBody(
                User::whereId($user_id)->pluck('device_key')->all(),
                $title,
                self::DISPLAY['transaction'],
                ""
            )
        );

        UserNotification::create([
            'user_id'   =>  $user_id,
            'type'  =>  'transaction',
            'title' =>  $title,
            'order_id' =>   0,
            'step'  =>  $result['msg']
        ]);
    }
}