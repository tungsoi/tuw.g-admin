<?php

namespace App\Console\Commands\System;

use App\Models\ReportWarehouse\ReportWarehouse;
use App\Models\ReportWarehouse\ReportWarehousePortal;
use Illuminate\Console\Command;

class CreateReportWarehousePortal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report-warehouse:portal';

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
        try {
            $report_warehouses = ReportWarehouse::select('title')->groupBy('title')->get();

            $exist_titles = [];
            foreach ($report_warehouses as $report_warehouse) {
                $exist_titles[$report_warehouse->title] = $report_warehouse->title;
                echo $report_warehouse->title . "\n";
                if ($report_warehouse->title != null) {
                    $data = $this->buildData($report_warehouse->title);

                    $flag = ReportWarehousePortal::whereTitle($report_warehouse->title)->first();
                    if ($flag) {
                        // update
                        ReportWarehousePortal::find($flag->id)->update([
                            'date'  =>  $data['date'],
                            'title' =>  $data['title'],
                            'count' =>  $data['count'],
                            'weight'  =>    $data['weight'],
                            'cublic_meter'  =>  $data['cublic_meter'],
                            'line'  =>  $data['line']
                        ]);
                    }
                    else {
                        // create
                        ReportWarehousePortal::create($data);
                    }
                }
            }

            $delete_rows = ReportWarehousePortal::whereNotIn('title', $exist_titles)->delete();
        }
        catch (\Exception $e) {
            dd($e->getMessage());
        }
        
    }

    public function buildData($title = "") {
        $records = ReportWarehouse::whereTitle($title)->get();
        $weight = 0;
        $cl = 0;
        foreach ($records as $row) {
            $weight += floatval(str_replace(',', '.', $row->weight));
            $cl += floatval(str_replace(',', '.', $row->cublic_meter));
        }
        
        return [
            'date'  =>  ReportWarehouse::whereTitle($title)->orderBy('date', 'asc')->first()->date,
            'title' =>  $title,
            'count' =>  ReportWarehouse::whereTitle($title)->count(),
            'weight'  =>  $weight,
            'cublic_meter'  =>  $cl,
            'offer_weight'  =>  0,
            'offer_cublic_meter'    =>  0,
            'line'  =>  "",
            'note'  =>  "",
        ];
    }
}
