<?php

namespace App\Console\Commands\Test;

use App\Admin\Services\UserService;
use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SyncData\AlilogiTransaction;
use App\Models\SyncData\AlilogiUser;
use App\Models\System\Transaction;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestWalletUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

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
        ini_set('memory_limit', '6400M');

        $orders = PaymentOrder::where('export_at', '>', '2021-10-01 00:00:01')
            ->where('status', '!=', 'cancel')
            // ->where('order_number', 'B7685')
            ->with('transportCode')
            ->get();

        
        foreach ($orders as $key => $order) {
            // dd($order);
            if ($order->transportCode->count() > 0) {

                try {
                    echo $key+1 . " -- ";
                    echo $order->order_number . " \t";

                    $ware_house_id = 0;

                    // dd($order->transportCode);
                    foreach ($order->transportCode as $transportCode) {
                        if ($transportCode->ware_house_id != null) {
                            $ware_house_id = $transportCode->ware_house_id;
                        }
                    }


                    // $ware_house_id = $order->transportCode->where('ware_house_id', '!=', null)->first()->ware_house_id;
                    echo $ware_house_id . "\n";
                    $order->warehouse_id = $ware_house_id;
                    $order->save();
                } catch (\Exception $e) {
                    dd($e->getMessage());
                    dd($order->transportCode);
                }
            }
           
        }

        dd('oke');
        // $orders = PurchaseOrder::where('status', 9)
        //                 ->where('success_at', '>=', "2021-10-01 00:00:01")
        //                 ->where('success_at', '<=', "2021-10-31 23:59:59")
        //                 ->with('items')
        //                 ->get();
        
        // $total = 0;
        // $note = [];
        // foreach ($orders as $key => $order) {
        //     $amount = number_format(str_replace(",", '', $order->amount()), 2, '.', '') ;
        //     $amount_vnd = $amount * $order->current_rate;
        //     $total += $amount_vnd;

        //     $note[] = ($key+1). " - ". $order->order_number . " - Tệ: " . $amount . " - Tỷ giá: " . $order->current_rate . " - VND: " . number_format($amount_vnd) . "\n";
        // }

        // Storage::disk('admin')->put('don_hang_thanh_cong_trong_thang_10.txt', $note);

        // dd(number_format($total));

        // $orders = PaymentOrder::whereStatus('payment_export')
        //     ->whereNull('export_at')
        //     ->get();

        //     dd($orders->count());
        // $temp = $orders->pluck('id')->toArray();

        // $orders = PaymentOrder::whereStatus('payment_export')
        //     ->whereBetween('created_at', ['2021-09-01', '2021-10-01'])
        //     // ->whereColumn('created_at','!=', 'updated_at')
        //     // ->whereNotNull('export_at')
        //     ->get();

        //     $temp2 = $orders->pluck('id')->toArray();
        // // foreach ($orders as $order) {
        // //     dd($order);
        // // }
        // dd(array_diff($temp2, $temp));
        // dd($orders->count());
        // dd($orders->sum('amount'));
        // $deposited = "245949";
        // $deposited_final = (int) floor($deposited /1000);

        //     $deposited_final *= 1000;
        //     dd($deposited_final);
        // $orderService = new OrderService();

        // $order = PurchaseOrder::find(21152);

        // // if ($order->status == $orderService->getStatus('new-order')) {

        // // amount item price
        //     $totalItemPrice = str_replace(',', '', $order->sumItemPrice());

        //     $percent = 70;
        //     $depositedRmb = $totalItemPrice / 100 * $percent;
        //     $depositedVnd = $depositedRmb * $order->current_rate;
        //     $deposited = number_format($depositedVnd, 0, '.', '');

        //     $deposited = floor($deposited/1000);
        //     $deposited *= 1000;
        //     $deposited = (int) $deposited;

        //     dd($deposited);

        //     $order->status = $orderService->getStatus('deposited');
        //     $order->deposited = $deposited;
        //     $order->deposited_at = now();
        //     $order->user_deposited_at = $this->user_created_id;
        //     $order->save();

        //     $job = new HandleCustomerWallet(
        //         $order->customer->id,
        //         $this->user_created_id, // admin
        //         $deposited,
        //         3,
        //         "Đặt cọc đơn hàng mua hộ $order->order_number"
        //     );
        //     dispatch($job);
        // }


        // $orders = PaymentOrder::whereStatus('cancel')->whereNotNull('transaction_note')->get();

        // $notes = $orders->pluck('transaction_note');

        // Transaction::whereIn('content', $notes)->delete();
        // // dd($transaction->count());
        // dd('oke');
        // $orders = PurchaseOrder::whereNotIn('final_payment', ["", 0])->where('created_at', 'like', '2021-09%')
        //     ->whereNotNull('final_payment')->orderBy('id', 'desc')->get();
        // dd($orders->count());
        // foreach ($orders as $order ) {
        //     echo $order->order_number."\n";

        //     $price_rmb = str_replace(",", "", $order->sumItemPrice());
        //     $ship = $order->sumShipFee();

        //     $amount = $price_rmb + $ship;

        //     $final_payment = str_replace(",", "", $order->final_payment);
                
        //     $order->offer_cn = number_format($amount - $final_payment, 2);
        //     $order->offer_vn = number_format(($amount - $final_payment) * $order->current_rate, 0);
        //     $order->save();
        // }
        // $total_vnd = 0;
        // $deposited = $orders->sum('deposited');

        // foreach ($orders as $order){
        //     $amount = (float) str_replace(",","", $order->sumItemPrice());
        //     $total_vnd = $amount * $order->current_rate;
        //     echo $order->id . "---" . number_format($total_vnd, 0) . "---" . number_format($order->deposited, 0) ."\n";
        // }
        
        // $money = rand(1000, 13000000);
        // $money = 3450;
        // // if ()
        // echo $money . "\n";
        // $money = floor($money/1000);
        // $money *= 1000;
        // $money = (int) $money;

        // dd($money);

        // $olds = AlilogiTransaction::where('customer_id', 1043)->get();

        // foreach ($olds as $transaction) {
        //     $flag = Transaction::where('customer_id', $transaction->customer_id)
        //         ->where('content', 'like', '%'.$transaction->content)
        //         ->get();
            
        //     if (! $flag->count() > 0) {

        //         echo $transaction->id . "\n";
        //     }
        // }

        // dd($olds->count());

        // $transactions = Transaction::where('content', 'like', '%ship%')
        //     ->orWhere('content', 'like', '%SHIP%')
        //     ->get();

        // foreach ($transactions as $transaction) {
        //     $old = AlilogiTransaction::where('content', $transaction->content)->first();

        //     $transaction->created_at = $old->created_at;
        //     $transaction->save();
        // }
        // $users = User::select('id', 'symbol_name', 'wallet')->whereIsCustomer(1)->get();
        // $ids = [];
        // foreach ($users as $user) {
        //     $service = new UserService();
        //     $data = $service->GetCustomerTransactionHistory($user->id, false);

        //     if (isset($data[0]) && $data[0]['after_payment'] != $user->wallet) {
        //         echo $user->id . " --- ";
        //         echo $user->symbol_name . " --- ";
        //         echo $user->wallet . " --- ";
        //         echo $data[0]['after_payment'] ."\n";

        //         $user->wallet = number_format($data[0]['after_payment'], 0, '.', '');
        //         $user->save();
        //     } else {
        //         echo $user->id. "-- done\n";
        //     }

        // }

        // dd($ids);
        // $code = [
        //     'KANGNAM',
        //     'LHTHU95',
        //     'UYEN230',
        //     'TRANGXUAN',
        //     'DUONG341',
        //     'VYVY',
        //     'KHONGTEN',
        //     'NAM85',
        //     'ALTKIEN',
        //     'ALKIMANH',
        //     'ALBQ95',
        //     'ALNINA',
        //     'ALTUAN2292',
        //     'ALMRSTHAO',
        //     'nguyet1808',
        //     'Yumi',
        //     'TXdothinga',
        //     'MinhChang',
        //     'HN.MEN',
        //     'xuanthanhfb',
        //     'MC95',
        //     'TXhieuvu',
        //     'Thuyvan96',
        //     'GIANGMUN',
        //     'HAU36',
        //     'nguyenhien02',
        //     'Huyen245',
        //     'Thuhien48',
        //     'Zangg',
        //     'TXAROOM',
        //     'TXvuan',
        //     'TXbaongoc95',
        //     'uyen264',
        //     'hienpham87',
        //     'Khanhly274',
        //     'Havy97',
        //     'KhLinh973',
        //     'thu123',
        //     'nga2003'
        // ];

        // $old_users = AlilogiUser::whereIsCustomer(1)->whereIn('symbol_name', $code)->get();

        // $key = 1;
        // $temp = 0;

        // $all = [];
        // foreach ($old_users as $key => $user) {
        //     // echo ($key+1) . ". ".$user->symbol_name . " --- Số dư hiện tại: " . number_format($user->wallet) . "\n";
        //     $user_id = $user->id;
        //     $old_transactions = AlilogiTransaction::whereCustomerId($user_id)->get();
        //     $new_transactions = Transaction::whereCustomerId($user_id)->get();

        //     $old_contents = $old_transactions->pluck('content', 'id');
        //     $new_contents = $new_transactions->pluck('content', 'id');

        //     $diff = array_diff($old_contents->toArray(),$new_contents->toArray());

        //     if (sizeof($diff) > 0) {
        //         foreach ($diff as $transaction_id => $transaction) {
        //             $flag = Transaction::where('content', $transaction)->first();

        //             if ($flag) {
        //                 echo "-- Đã tồn tại \n";

        //                 echo $transaction_id . "-". $transaction;


        //             } else {

        //                 // echo "-- Chưa tồn tại \n";

        //                 // $raw = AlilogiTransaction::find($transaction_id);

        //                 // Transaction::create($raw->toArray());
        //             }
        //         }
        //     }
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