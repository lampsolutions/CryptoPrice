<?php

namespace App\Lib;

class ProCoinMarketCapApi {

    const API_URL = "https://pro-api.coinmarketcap.com";

    protected $api_token;

    public function __construct($api_token) {
        $this->api_token = $api_token;
    }

    public function getMarketQuotes(array $ids, array $symbols, array $convert) {
        return $this->call_api(
            '/v1/cryptocurrency/quotes/latest',
            [
                'query' => [
                    'symbol' => implode(',', $symbols),
                    'convert' => implode(',', $convert),
                ]
            ]
        );
    }

    protected function call_api($endpoint, $options=[]) {

        $client = new \GuzzleHttp\Client();

        // Add Auth Token
        $options['headers']['X-CMC_PRO_API_KEY'] = $this->api_token;

        $response = $client->get(ProCoinMarketCapApi::API_URL.$endpoint, $options);

        if($response->getStatusCode() == "200") {
            return \GuzzleHttp\json_decode($response->getBody());
        } else {
            return false;
        }
    }
}
