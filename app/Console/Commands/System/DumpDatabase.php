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
        $env = config('database.connections.alilogi-portal');

        $filename = "alilogi-portal-backup-" . Carbon::now()->format('YmdHis') . ".sql";

        $command = "mysqldump -u " . $env['username'] ." -p " . $env['password'] ." ". $env['database'] . " > " . storage_path() . "/app/backup/" . $filename;
        
        $returnVar = NULL;
        $output  = NULL;
  
        exec($command, $output, $returnVar);
    }
}
