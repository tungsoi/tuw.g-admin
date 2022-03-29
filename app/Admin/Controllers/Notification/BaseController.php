<?php

namespace App\Admin\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\User;

class BaseController extends Controller
{
    public function index() {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $FcmToken = User::whereNotNull('device_key')->pluck('device_key')->all();
        
        $serverKey = 'AAAAmCs1ZTI:APA91bEhjfd5zbAn_5wQFt7sCKlclpAbDCQ4auPfyMpHP6md6t_BuRG8A20wGQVRw1bgXDs02amy0ByeXAUIZJsQ0SZHJmeaQfeAKr5JaF36YLpgXF9dOPsHAnwBaqLqqkDCIM3TmU8j';

        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => 'Alilogi',
                "body" => [
                    "content"   =>  "This is test",  
                    "display"   =>  "transport_order_detail_screen", // transaction_screen, purchase_order_detail_screen, transport_code_list_screen
                    "id"        =>  42064
                ],
                "icon"  =>  "https://img.icons8.com/doodle/2x/tow-truck--v1.png 2x"
            ]
        ];
        $encodedData = json_encode($data);
    
        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }        

        curl_close($ch);
        
        return response()->json([
            'status'    =>  true,
            'msg'   => $result
        ]);
    }
}