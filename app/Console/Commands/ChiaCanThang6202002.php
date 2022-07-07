<?php

namespace App\Console\Commands;

use App\Models\System\TransactionWeight;
use App\User;
use Illuminate\Console\Command;

class ChiaCanThang6202002 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chiacan:dot2';

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

// 4.SgNhuPhuong : 5kg
        $data = "1.SgTrang2006 : 5kg
2.SgMinhTuan : 5kg
3.SgNgocthao123 : 5kg
4.NhuPhuong : 5kg
5.SgHuynh : 5kg
6.SgNhiluu : 5kg
7.SgKimanh19 : 5kg
8.Anhdaklak : 15kg
9.Buuduyen : 15kg
10.HCMminhngoc22 : 5kg
11.SgAnhMinh : 5kg
12.SgBaochau : 5kg
13.SgBoiboi : 10kg
14.SgTeemay : 10kg
15.SgTram1808 : 5kg
16.SgBangtran11 : 10kg
17.SgTramtran13 : 5kg
18.SgAn081 : 5kg
19.SgThucNhu : 5kg
20.SgTu27 : 10kg
21.SgHnhu123 : 5kg
22.SgThuyAn : 5kg 
23.SgTran51 : 5kg
24.SgThukiet1010 : 15kg
25.SgTranTran : 10kg
26.SgSanAnh2104 : 5kg
27.SgKhanhNhi : 5kg
28.SgQue : 10kg
29.Minhtu : 5kg
30.SgLuthao : 3kg
31.SgVan : 5kg
32.SgNhungChau : 5kg
33.SgDong18 : 5kg
34.SgHao97 : 3kg
35.SgHongThuy98 : 5kg
36.SgLinhVo : 40kg
37.SgXieu : 5kg";

        $array = explode("\n", $data);

        $temp = [];
        $sum = 0;
        foreach ($array as $row) {
            $string = explode(" : ", $row);

            $symbol_name = explode(".", $string[0])[1];
            $kg = (int) str_replace("kg", "", $string[1]);

            $sum += $kg;
            $temp[] = [
                'kg'    =>  $kg,
                'symbol_name'   =>  $symbol_name
            ];
        }

        foreach ($temp as $user) {
            $flag = User::whereSymbolName($user['symbol_name'])->first();
            echo $flag->id . "\n";
            if ($flag) {
                
                $data = [
                    'customer_id'   =>  $flag->id,
                    'user_id_created'   =>  2455,
                    'content'   =>  'Chương trình tri ân khảo sát khách hàng tháng 06-2022 - Khách sài gòn',
                    'updated_user_id'   =>  2455,
                    'kg'        =>  (int) $user['kg'],
                    'type'  =>  2
                ];

                $sum += (int) $user['kg'];

                TransactionWeight::create($data);

                // cong can vao tai khoan khach
                $flag->wallet_weight += (int) $user['kg'];
                $flag->save();

                $flag_x[] = [
                    'user_id'   =>  $flag->id,
                    'kg'    =>  (int) $user['kg']
                ];
            }
        }

        dd($flag_x);
    }
}
