<?php

namespace App\Console\Commands\SyncData;

use App\Models\SyncData\AlilogiTransportCode;
use App\Models\TransportOrder\TransportCode as TransportCodeModel;
use Illuminate\Console\Command;

class TransportCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-data:transport_code';

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
        // TransportCodeModel::truncate();
        $oldData = AlilogiTransportCode::where('id', '>', '45592')->chunk(5000, function ($rows) {
            foreach ($rows as $key => $row) {
                // if (! TransportCodeModel::where('transport_code', $row->cn_code)->first()) {
                    echo $key . " - " . $row->cn_code . "\n";
                //     try {
                        $data = [
                            'id'    =>  $row->id,
                            'transport_code'    =>  $row->cn_code,
                            'kg'    =>  $row->kg,
                            'length'    =>  $row->product_length,
                            'width'     =>  $row->product_width,
                            'height'    =>  $row->product_height,
                            'order_id'    =>  $row->order_id,
                            'price_service' =>  $row->price_service,
                            'advance_drag'  =>  $row->advance_drag,
                            'status'    =>  $this->getStatus(
                                $row->warehouse_cn, $row->warehouse_cn_date,
                                $row->warehouse_vn, $row->warehouse_vn_date,
                                $row->is_payment
                            ),
                            'china_receive_at'  =>  $row->warehouse_cn_date,
                            'vietnam_receive_at'    =>  $row->warehouse_vn_date,
                            'waitting_payment_at'   =>  null,
                            'payment_at'    =>  $row->order ? $row->order->created_at : null,
                            'begin_swap_warehouse_at'   =>  null,
                            'finish_swap_warehouse_at'  =>  null,
                            'admin_note'    =>  $row->note,
                            'customer_note' =>  null,
                            'customer_code_input'   =>  $row->customer_name,
                            'ware_house_id' =>  $row->ware_house_id,
                            'payment_type'  =>  $row->payment_type
                        ];
    
                        $newData = TransportCodeModel::firstOrCreate($data);

                        if ($newData->id != $row->id) {
                            echo "id moi: " . $newData->id . " ---> id alilogi cu: " . $row->id . "\n";
                            dd('fail');
                        }
                //     } catch (\Exception $e) {
                //         echo "- Error: ".$e->getMessage();
                //         // dd($data);
                //     }
                // } else {
                //     echo "- Exitse: $row->cn_code \n";
                // }
                
            }
        });

       
    }

    public function getStatus($isChinaRe, $dateChinaRe, $isViRe, $dateViRe, $isPayment) {

        // const CHINA_RECEIVE = 0;
        // const VIETNAM_RECEIVE = 1;
        // const WAITTING_PAYMENT = 2;
        // const PAYMENT = 3;
        // const SWAP_WAREHOUSE = 4;

        if ($isPayment == 1) {
            return 3;
        } else {
            if ($isViRe == 1 && $dateViRe != "") {
                return 1;
            } else {
                return 0;
            }
        }

        return "";
    }
}
