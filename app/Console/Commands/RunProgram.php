<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunProgram extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run-program {seconds=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connects to api and logs order book for currency pair';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info( 'The command was successful!' );
    }
}
