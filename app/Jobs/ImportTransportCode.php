<?php

namespace App\Jobs;

use App\Models\TransportOrder\TransportCode;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportTransportCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $date;
    protected $user_created_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $date, $user_created_id)
    {
        $this->data = $data;
        $this->date = $date;
        $this->user_created_id = $user_created_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->data as $key => $row) {
            if (is_array($row) && sizeof($row) >= 1) {

                $item = array_values($row);
                if (isset($item[0]) && $item[0] != null) {
                    $temp = [
                        'transport_code' => (string) sprintf("%d", $item[0]),
                        'advance_drag'   => $item[1] ?? 0,
                        'china_receive_at'  =>  $this->date. " 00:00:01",
                        'china_receive_user_id' =>  $this->user_created_id,
                        'internal_note' =>  'import',
                        'status'    =>  0
                    ];

                    $flag = TransportCode::whereTransportCode($temp['transport_code'])->count();

                    if ($flag == 0) {
                        TransportCode::firstOrCreate($temp);
                    }
                }
            }
        }
    }
}
