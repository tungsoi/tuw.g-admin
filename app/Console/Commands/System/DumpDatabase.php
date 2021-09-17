<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;
use Carbon\Carbon;

class DumpDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

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
        $owed = -1232132323;
        $owed = abs($owed);

        dd($owed);
        // $filename = "backup-" . Carbon::now()->format('Y-m-d') . ".gz";
  
        // $command = "mysqldump -h " . env('DB_HOST') .
        // " -u "          . env('DB_USERNAME') .
        // " -p\""         . env('DB_PASSWORD') . "\"" .
        // " --databases " . env('DB_DATABASE');

        // $returnVar = NULL;
        // $output  = NULL;
  
        // exec($command, $output, $returnVar);
    }
}
