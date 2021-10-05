<?php

namespace App\Console\Commands\System;

use App\Models\System\ScheduleLog;
use App\Models\TransportOrder\TransportCode;
use Illuminate\Console\Command;

class DeleteErrorTransportCodeChina extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:error-transport-code-china';

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
        TransportCode::where('status', 0)
            ->where('internal_note', '!=', 'import')
            ->delete();

        ScheduleLog::create([
            'name'  =>  $this->signature
        ]);
    }
}
