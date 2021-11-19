<?php

namespace App\Console\Commands;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\TransportOrder\TransportCode;
use Illuminate\Console\Command;

class CalculatorM3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cal:m3';

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
        // $codes = TransportCode::select('id', 'transport_code', 'length', 'width', 'height', 'm3')
        // ->whereIn('m3', [0.00, 0.000, ""])
        // ->where('length', 0)
        // ->where('width', 0)
        // ->where('height', 0)
        // ->whereNull('m3')
        // ->where('vietnam_receive_at', '>=', '2021-09-01 00:00:01')
        // ->where('vietnam_receive_at', '<=', '2021-09-30 00:00:01')
        // ->update([
        //     'm3'    =>  "0.000"
        // ]);
        // ->get();

        // $codes = TransportCode::whereNull('width')->update([
        //     'width' => 0
        // ]);

        // dd($codes->count());
        // dd($codes);

        $order_ids = PaymentOrder::select('created_at', 'order_number', 'total_kg', 'total_m3', 'id', 'discount_value', 'discount_type', 'is_sub_customer_wallet_weight', 'total_sub_wallet_weight')
        ->where('export_at', '>', '2021-10-01 00:00:01')
        // ->where('export_at', '<', '2021-10-31 00:00:00')
        ->pluck('id');

        $codes = TransportCode::select('id', 'transport_code', 'length', 'width', 'height', 'm3')
        ->whereIn('m3', [0.00, 0.000])
        ->where('length', '!=', 0)
        ->where('width', '!=', 0)
        ->where('height', '!=', 0)
        // ->where('vietnam_receive_at', '>=', '2021-09-01 00:00:01')
        // ->where('vietnam_receive_at', '<=', '2021-09-30 00:00:01')
        ->whereIn('order_id', $order_ids)
        ->orderBy('id', 'desc')
        // ->get();
        // dd($codes->count());

        // foreach ($codes as $code) {
        //     echo $code->transport_code . ": ". $code->length."-".$code->width."-".$code->height."-".$code->m3."\n";
        // }

        // dd($codes->count());
        // dd($codes->pluck('transport_code'));


        // da ve vn tu 01-10 , m3 = 0 : 27254
        // da ve vn tu 01-10 , m3 != 0 : 46
        // da ve vn tu 01-10 , m3 null : 1870
        // dd($codes);
        ->chunk(1000, function ($codes) {
            foreach ($codes as $key => $code) {
                echo ($key+1) . "-". $code->transport_code. "\n";

                $width = ($code->width != "") ? $code->width : 0;
                $height = ($code->height != "") ? $code->height : 0;
                $length = ($code->length != "") ? $code->length : 0;

                $m3 = number_format(($width * $height * $length)/1000000, 3);
                $code->m3 = $m3;
                $code->save();
            }
        });
    }
}
