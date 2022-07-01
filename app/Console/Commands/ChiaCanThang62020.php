<?php

namespace App\Console\Commands;

use App\Models\System\TransactionWeight;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ChiaCanThang62020 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chiacan:thang6';

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
        //
        $data = array(
            array("val0"=>"10","val1"=>"NGOC HOA "),
            array("val0"=>"20","val1"=>"MAI 65"),
            array("val0"=>"10","val1"=>"TRANG LÊ"),
            array("val0"=>"5","val1"=>"VNKDUY20"),
            array("val0"=>"10","val1"=>"ALKIMANH"),
            array("val0"=>"5","val1"=>"PHONG TRANH "),
            array("val0"=>"50","val1"=>"ALTKIEN "),
            array("val0"=>"50","val1"=>"TP88"),
            array("val0"=>"5","val1"=>"LUU DIEU HÀ"),
            array("val0"=>"5","val1"=>"ANH2305"),
            array("val0"=>"20","val1"=>"TXPHUONG"),
            array("val0"=>"20","val1"=>"HANH01 "),
            array("val0"=>"15","val1"=>"DHDO"),
            array("val0"=>"10","val1"=>"HUYEN93 "),
            array("val0"=>"10","val1"=>"HUE HAN"),
            array("val0"=>"10","val1"=>"HƯƠNG DAO"),
            array("val0"=>"5","val1"=>"VTS2907 "),
            array("val0"=>"5","val1"=>"HANHLE "),
            array("val0"=>"25","val1"=>"NAM 85 "),
            array("val0"=>"5","val1"=>"SGTRAM"),
            array("val0"=>"5","val1"=>"NHATHUNG95 "),
            array("val0"=>"5","val1"=>"H2O"),
            array("val0"=>"10","val1"=>"LINHCHI 462 "),
            array("val0"=>"5","val1"=>"PHUONGLINH469 "),
            array("val0"=>"5","val1"=>"KH2610 "),
            array("val0"=>"10","val1"=>"DUYBERRY "),
            array("val0"=>"5","val1"=>"7THIENQUANG"),
            array("val0"=>"5","val1"=>"MTHAI"),
            array("val0"=>"5","val1"=>"HNTHUYLINH"),
            array("val0"=>"5","val1"=>"MAIANH033"),
            array("val0"=>"5","val1"=>"TRONG 0410"),
            array("val0"=>"5","val1"=>"CHINH998"),
            array("val0"=>"5","val1"=>"HBTALOHA "),
            array("val0"=>"5","val1"=>"TXHOANGHUYEN"),
            array("val0"=>"15","val1"=>"LINH991"),
            array("val0"=>"5","val1"=>"PA915 "),
            array("val0"=>"5","val1"=>"VNKDUY20 "),
            array("val0"=>"5","val1"=>"MINHTU"),
            array("val0"=>"5","val1"=>"HONG82"),
            array("val0"=>"30","val1"=>"PHUNGSG"),
            array("val0"=>"5","val1"=>"DUNG17 "),
            array("val0"=>"10","val1"=>"TRANGLE "),
            array("val0"=>"5","val1"=>"PHUONG261 "),
            array("val0"=>"5","val1"=>"VAN05"),
            array("val0"=>"5","val1"=>"VITKOI94"),
            array("val0"=>"30","val1"=>"EDONG"),
            array("val0"=>"10","val1"=>"TRANG84"),
            array("val0"=>"5","val1"=>"LEDUNG"),
            array("val0"=>"5","val1"=>"KHAVI126"),
            array("val0"=>"5","val1"=>"KHLINH973"),
            array("val0"=>"20","val1"=>"GAU"),
            array("val0"=>"15","val1"=>"HAU36"),
            array("val0"=>"5","val1"=>"KHANHLINH "),
            array("val0"=>"5","val1"=>"MKH05171"),
            array("val0"=>"5","val1"=>"THANHTHAO555"),
            array("val0"=>"5","val1"=>"UYEN00"),
            array("val0"=>"5","val1"=>"THU09"),
            array("val0"=>"5","val1"=>"HANH1711"),
            array("val0"=>"5","val1"=>"NSON03"),
            array("val0"=>"10","val1"=>"TRANGXUAN"),
            array("val0"=>"5","val1"=>"TXYEN95 "),
            array("val0"=>"10","val1"=>"QUANGDIEU"),
            array("val0"=>"5","val1"=>"HUONG0211"),
            array("val0"=>"5","val1"=>"ALNHUNGNGUYEN90"),
            array("val0"=>"5","val1"=>"DIEUNGAN657"),
            array("val0"=>"20","val1"=>"DONHUNG"),
            array("val0"=>"5","val1"=>"MY1611"),
            array("val0"=>"5","val1"=>"HUONGLY97"),
            array("val0"=>"5","val1"=>"JOOJAE313"),
            array("val0"=>"5","val1"=>"GIANG1293"),
            array("val0"=>"5","val1"=>"TDOAN"),
            array("val0"=>"10","val1"=>"DUONG09"),
            array("val0"=>"5","val1"=>"PHUONGANH155"),
            array("val0"=>"5","val1"=>"THUNGAN639"),
            array("val0"=>"20","val1"=>"NGHIATQ"),
            array("val0"=>"5","val1"=>"ALLEGIANG"),
            array("val0"=>"5","val1"=>"CONGMINH "),
            array("val0"=>"5","val1"=>"HBTMYHAO"),
            array("val0"=>"20","val1"=>"THIEN"),
        );

        $temp = [];
        foreach ($data as $row) {
            $symbol_name = str_replace("-", "", str_slug(Str::lower($row['val1'])));
            $temp[] = [
                'kg'    =>  $row['val0'],
                'symbol_name'   =>  $symbol_name
            ];
        }

        $sum = 0;
        $flag = [];
        foreach ($temp as $customer) {
            $user = User::whereSymbolName($customer['symbol_name'])->first();

            if ($user) {
                // dd($customer);

                // tao record chia can trong transaction weight
                $data = [
                    'customer_id'   =>  $user->id,
                    'user_id_created'   =>  2455,
                    'content'   =>  'Chương trình tri ân khảo sát khách hàng tháng 06-2022',
                    'updated_user_id'   =>  2455,
                    'kg'        =>  (int) $customer['kg'],
                    'type'  =>  2
                ];

                $sum += (int) $customer['kg'];

                TransactionWeight::create($data);

                // cong can vao tai khoan khach
                $user->wallet_weight += (int) $customer['kg'];
                $user->save();

                $flag[] = [
                    'user_id'   =>  $user->id,
                    'kg'    =>  (int) $customer['kg']
                ];
            }
        }


        dd($sum, $flag);
        // dd($temp);
    }
}
