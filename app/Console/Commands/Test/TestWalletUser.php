<?php

namespace App\Console\Commands\Test;

use App\Models\SyncData\AlilogiTransaction;
use App\Models\SyncData\AlilogiUser;
use App\Models\System\Transaction;
use Illuminate\Console\Command;

class TestWalletUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-wallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $code = [
            'KANGNAM',
            'LHTHU95',
            'UYEN230',
            'TRANGXUAN',
            'DUONG341',
            'VYVY',
            'KHONGTEN',
            'NAM85',
            'ALTKIEN',
            'ALKIMANH',
            'ALBQ95',
            'ALNINA',
            'ALTUAN2292',
            'ALMRSTHAO',
            'nguyet1808',
            'Yumi',
            'TXdothinga',
            'MinhChang',
            'HN.MEN',
            'xuanthanhfb',
            'MC95',
            'TXhieuvu',
            'Thuyvan96',
            'GIANGMUN',
            'HAU36',
            'nguyenhien02',
            'Huyen245',
            'Thuhien48',
            'Zangg',
            'TXAROOM',
            'TXvuan',
            'TXbaongoc95',
            'uyen264',
            'hienpham87',
            'Khanhly274',
            'Havy97',
            'KhLinh973',
            'thu123',
            'nga2003'
        ];

        $old_users = AlilogiUser::whereIsCustomer(1)->whereIn('symbol_name', $code)->get();

        $key = 1;
        $temp = 0;

        $all = [];
        foreach ($old_users as $key => $user) {
            // echo ($key+1) . ". ".$user->symbol_name . " --- Số dư hiện tại: " . number_format($user->wallet) . "\n";
            $user_id = $user->id;
            $old_transactions = AlilogiTransaction::whereCustomerId($user_id)->get();
            $new_transactions = Transaction::whereCustomerId($user_id)->get();

            $old_contents = $old_transactions->pluck('content', 'id');
            $new_contents = $new_transactions->pluck('content', 'id');

            $diff = array_diff($old_contents->toArray(),$new_contents->toArray());

            if (sizeof($diff) > 0) {
                foreach ($diff as $transaction_id => $transaction) {
                    echo $transaction_id . " --- " . $transaction . "\n";
                }
            }
            // foreach ($old_content)


            // if (sizeof ($result) > 0) {
            //     $key_row = 0;
            //     foreach ($result as $content) {
            //         $res = AlilogiTransaction::where('content', $content)->first();
            //         $flag = Transaction::where('content', $content)->first();
            //         if (! $flag) {
            //             // chua ton tai trong data moi

            //             $temp++;
            //             // $all[] = $old_record_id;
            //             // echo " --- Chưa tồn tại\n";

            //             // $create = $res->toArray();
            //             // $create['note'] = $create['id'];

            //             // dd($create);
            //             // Transaction::create($create);

            //         //     dd('okd');
            //         } 
                    
            //         else {
            //         //     $all[] = $old_record_id;

            //         echo ($key+1) . "." . ($key_row+1) . ". ".$res->id."/" . $res->content . " --- " . number_format($res->money) . " --- " . $res->created_at;
            //             echo " --- Đã tồn tại\n";
            //         }
            //         $key_row++;
            //     }
            //     // echo $key . " --- ". $user->symbol_name . " --- " . json_encode($result) . "\n";
            //     $key++;
            // }

            // echo "------------------------\n";
        }

    }
}



// 1. KANGNAM --- -89,639,910
// 2. LHTHU95 --- -528,300
// 3. UYEN230 --- -1,196,700
// 4. TRANGXUAN --- -414,300
// 5. DUONG341 --- -1,691,170
// 6. VYVY --- -12,748,300
// 7. KHONGTEN --- 0
// 8. NAM85 --- -2,258,725
// 9. ALTKIEN --- -71,170,084
// 10. ALKIMANH --- 6,810,215
// 11. ALBQ95 --- -1,294,487
// 12. ALNINA --- 5,608,946
// 13. ALTUAN2292 --- -10,641,928
// 14. ALMRSTHAO --- 256,136
// 15. nguyet1808 --- -48,848
// 16. Yumi --- 894,564
// 17. TXdothinga --- -270,760
// 18. MinhChang --- -510,200
// 19. HN.MEN --- 125,902
// 20. xuanthanhfb --- -4,387,438
// 21. MC95 --- -1,063,243
// 22. TXhieuvu --- 408,038
// 23. Thuyvan96 --- 245,131
// 24. GIANGMUN --- -1,696,200
// 25. HAU36 --- -3,369,400
// 26. nguyenhien02 --- 1,385
// 27. Huyen245 --- 213,260
// 28. Thuhien48 --- -8,317,675
// 29. Zangg --- 1,642,079
// 30. TXAROOM --- 577
// 31. TXvuan --- 59,080
// 32. TXbaongoc95 --- -48,643,224
// 33. uyen264 --- 600
// 34. hienpham87 --- 1,274
// 35. Khanhly274 --- -660,262
// 36. Havy97 --- 479
// 37. KhLinh973 --- 700
// 38. thu123 --- -120,739
// 39. nga2003 --- -6,647,609