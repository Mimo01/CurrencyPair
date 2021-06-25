<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApiController;
use App\Models\Bid;
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

    private $api;

    // bad constants, should be put somewhere else
    private $currencyFrom = 'BTC';
    private $currencyTo = 'EUR';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->api = new ApiController( $this->currencyFrom, $this->currencyTo );
    }

    /**
     * Parse bid data
     *
     * @param array $bids
     * @param bool $selling
     *
     * @return array
     */
    private function parseBids( array $bids, bool $selling ): array {
        $result = [];
        foreach ( $bids as $data ) {
            $bid                = new Bid;
            $bid->currency_from = $this->currencyFrom;
            $bid->currency_to   = $this->currencyTo;
            $bid->price         = $data->price;
            $bid->amount        = $data->amount;
            $bid->selling       = $selling;
            if ( $bid->is_sufficient() ) {
                $result[] = $bid;
            }
        }

        return $result;
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        $orderBook = $this->api->getOrderBook();
        $askData   = $orderBook->data->asks;
        $bidData   = $orderBook->data->bids;
        $result    = array_merge( $this->parseBids( $askData, false ), $this->parseBids( $bidData, true ) );
        array_filter( $result, function ( $bid ) {
            return $bid->is_sufficient();
        } );
        foreach ( $result as $bid ) {
            $bid->save();
            $this->info( $bid );
        }
    }
}
