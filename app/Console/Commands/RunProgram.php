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
    private $minValue = 500;

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
            if ( $bid->get_value() > $this->minValue ) {
                $result[] = $bid;
            }
        }

        return $result;
    }

    /**
     * Find bid with highest value
     *
     * @param array $bids
     *
     * @return Bid|null
     */
    private static function findBest( array $bids ): ?Bid {
        return array_reduce( $bids, function ( $best, $next ) {
            return ( $best ? $best->get_value() : 0 ) > $next->get_value() ? $best : $next;
        }, null );
    }

    private function nextCall() {
        $orderBook = $this->api->getOrderBook();
        $askData   = $orderBook->data->asks;
        $bidData   = $orderBook->data->bids;
        $asks      = $this->parseBids( $askData, false );
        $bids      = $this->parseBids( $bidData, true );
        $best      = [ 'ask' => $this->findBest( $asks ), 'bid' => $this->findBest( $bids ) ];
        foreach ( $best as $bid ) {
            if ( $bid ) {
                $bid->save();
                $this->info( $bid );
            }
        }
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        while (true) {
            $this->nextCall();
            sleep($this->argument('seconds'));
        }
    }
}
