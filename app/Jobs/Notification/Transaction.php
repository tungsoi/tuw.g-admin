<?php

namespace App\Jobs\Notification;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Transaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $user_id;
    protected $title;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $title)
    {
        $this->user_id = $user_id;
        $this->title = $title;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $serverKey = 'AAAAmCs1ZTI:APA91bEhjfd5zbAn_5wQFt7sCKlclpAbDCQ4auPfyMpHP6md6t_BuRG8A20wGQVRw1bgXDs02amy0ByeXAUIZJsQ0SZHJmeaQfeAKr5JaF36YLpgXF9dOPsHAnwBaqLqqkDCIM3TmU8j';

        $data = [
            "registration_ids" => User::whereId($this->user_id)->pluck('device_key')->all(),
            "notification" => [
                "title"     =>  'Alilogi Thông Báo',
                "body"      =>  $this->title,
                "icon"      =>  "https://img.icons8.com/doodle/2x/tow-truck--v1.png 2x",
            ],
            'data' => [
                "display"   =>  "transaction_screen"
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
        
        return response()->json([
            'status'    =>  true,
            'msg'   => $result
        ]);
    }
}
