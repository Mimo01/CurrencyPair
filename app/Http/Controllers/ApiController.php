<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller {
    /**
     * Url where to find api
     *
     * @var string
     */
    private $url = 'https://coinmate.io/api/';

    /**
     * @var string
     */
    private $currencyFrom;

    /**
     * @var string
     */
    private $currencyTo;

    /**
     * Api constructor.
     *
     * @param $currencyFrom
     * @param $currencyTo
     */
    public function __construct( $currencyFrom, $currencyTo ) {
        $this->currencyFrom = $currencyFrom;
        $this->currencyTo   = $currencyTo;
    }

    private function getCurrencyPair(): string {
        return $this->currencyFrom . '_' . $this->currencyTo;
    }

    /**
     * Make api call on provided url
     *
     * @param string $url
     *
     * @return mixed|null
     */
    private static function callApi( string $url ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, [ "Content-Type: application/x-www-form-urlencoded" ] );

        $response  = curl_exec( $ch );
        $error     = curl_error( $ch );
        $errorCode = curl_errno( $ch );

        curl_close( $ch );

        if ( $error ) {
            error_log( "Error $errorCode: $error" );

            return null;
        }

        return json_decode( $response );
    }

    /**
     * Get order book
     *
     * @return array [asks, bids]
     */
    public function getOrderBook(): array {
        $requestUrl = $this->url . 'orderBook?' . http_build_query( [
                'currencyPair'      => $this->getCurrencyPair(),
                'groupByPriceLimit' => true
            ] );
        $response   = $this->callApi( $requestUrl );
        if ( ! $response ) {
            return [ 'asks' => [], 'bids' => [] ];
        }
        if ( $response->error ) {
            error_log( $response->errorMessage );

            return [ 'asks' => [], 'bids' => [] ];
        }

        return [ 'asks' => $response->data->asks, 'bids' => $response->data->bids ];
    }
}
