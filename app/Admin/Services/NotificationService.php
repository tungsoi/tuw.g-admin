<?php

namespace App\Admin\Services;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\UserNotification;
use App\User;

class NotificationService {
    public function sendTransportOrder($user_id, $order_id) 
    {
        $serverKey = 'AAAAmCs1ZTI:APA91bEhjfd5zbAn_5wQFt7sCKlclpAbDCQ4auPfyMpHP6md6t_BuRG8A20wGQVRw1bgXDs02amy0ByeXAUIZJsQ0SZHJmeaQfeAKr5JaF36YLpgXF9dOPsHAnwBaqLqqkDCIM3TmU8j';
        $order_number = PaymentOrder::find($this->order_id)->order_number;
        $title = "Bạn có 1 đơn hàng mới chờ xuất kho - Mã đơn hàng ".$order_number.". Bạn đến lấy hàng sớm nhé !";
        $data = [
            "registration_ids" => User::whereId($this->user_id)->pluck('device_key')->all(),
            "notification" => [
                "title"     =>  'Alilogi Thông Báo',
                "body"      =>  $title,
                "icon"      =>  "https://img.icons8.com/doodle/2x/tow-truck--v1.png 2x",
            ],
            'data' => [
                "display"   =>  "transport_order_detail_screen",
                "id"        =>  $this->order_id
            ]
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }        

        curl_close($ch);

        UserNotification::create([
            'user_id'   =>  $this->user_id,
            'type'  =>  'transport_order',
            'title' =>  $title,
            'order_id' =>   $this->order_id,
            'step'  =>  $result === FALSE ? json_encode(curl_error($ch)) : "done"
        ]);
        
        return response()->json([
            'status'    =>  true,
            'msg'   => $result
        ]);
    }
}