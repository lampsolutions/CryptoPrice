<?php

namespace App\Http\Controllers;

use App\CryptoCurrency;
use App\CryptoCurrencyRate;
use App\Enums\Fiat\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class CurrencyController extends Controller {
    /**
     * @OA\Post(
     *     path="/api/v1/calculate-exchange",
     *     tags={"exchange"},
     *     summary="Calculates currency exchange",
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="Origin currency amount",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="origin",
     *         in="query",
     *         description="Origin currency symbol",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"USD", "EUR", "CHF", "BTC", "DASH", "LTC", "BCH", "ETH"}
     *
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="destination",
     *         in="query",
     *         description="Destination currency symbol",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"BTC", "DASH", "LTC", "BCH", "ETH", "USD", "EUR", "CHF"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="api",
     *         in="query",
     *         description="Market pricing data origin api",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"coinmarketcap.com", "kraken.com", "coinbase.com"}
     *         )
     *     ),
     *     operationId="Info",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="bad request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error"
     *     )
     * )
     *
     * @param Request $request
     * @return mixed
     */
    public function CalculateExchange(Request $request) {
        $amount = $request->get('amount');
        $origin = $request->get('origin');
        $destination = $request->get('destination');
        $api = empty($request->get('api')) ? 'coinmarketcap.com' : $request->get('api');

        try {
            $currency = CryptoCurrency::where('symbol', '=' , $destination)->firstOrFail();
            $currency_rate = $currency->rates()
                ->where('api', $api)
                ->where('currency', $origin)
                ->orderBy('last_updated', 'DESC')
                ->firstOrFail();

            $price = bcdiv($amount, $currency_rate->price, (int)env('CURRENCY_EXCHANGE_PRECISION'));

            return [
                'amount' => $price,
                'currency' => $currency->symbol,
                'api' => $currency_rate->api,
                'last_updated' => date(DATE_ATOM, strtotime($currency_rate->last_updated))
            ];
        } catch (\Exception $e) {
            return new Response('', 400);
        }
    }


    private function fiat_to_coin($amount, $rate) {
        $coin_amount = ( (float)$amount * (float)$rate->price );
        return round($coin_amount, (int)env('CURRENCY_EXCHANGE_PRECISION'));
    }

    private function coin_to_fiat($amount, $rate) {
        $fiat_amount = ( (float)$amount * (1/(float)$rate->price ));
        return round($fiat_amount, (int)env('CURRENCY_EXCHANGE_PRECISION'));
    }

    /**
     * @param $coin
     * @param $fiat
     * @param bool $api
     * @return CryptoCurrencyRate
     */
    private function getRate($coin,$fiat,$dateTime=false,$api=false){
        try {
            $unixTime = strtotime($dateTime);
            $dateFormattedSafe = date(DATE_ATOM, $unixTime);
            $currency = CryptoCurrency::where('symbol', '=' , $coin)->firstOrFail();
            $currency_rate = $currency->rates()
                ->where('api', $api)
                ->where('last_updated', '>=', date(DATE_ATOM, strtotime('-3 days', $unixTime)))
                ->where('last_updated', '<=', date(DATE_ATOM, strtotime('+3 days', $unixTime)))
                ->where('currency', $fiat)
                ->orderByRaw('ABS( TIMESTAMPDIFF(SECOND, last_updated, "'.$dateFormattedSafe.'" ) )')
                ->firstOrFail();

            return $currency_rate;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/calculate-historic-exchange",
     *     tags={"exchange"},
     *     summary="Calculates historical currency exchange",
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="Origin currency amount",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="origin",
     *         in="query",
     *         description="Origin currency symbol",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"USD", "EUR", "CHF", "BTC", "DASH", "LTC", "BCH", "ETH"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="datetime",
     *         in="query",
     *         description="Origin currency symbol",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="date-time",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="destination",
     *         in="query",
     *         description="Destination currency symbol",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"BTC", "DASH", "LTC", "BCH", "ETH", "USD", "EUR", "CHF"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="api",
     *         in="query",
     *         description="Market pricing data origin api",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"coinmarketcap.com", "kraken.com", "coinbase.com"}
     *         )
     *     ),
     *     operationId="Info",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="bad request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error"
     *     )
     * )
     *
     * @param Request $request
     * @return mixed
     */
    public function CalculateHistoricExchange(Request $request) {
        $amount = $request->get('amount');
        $origin = $request->get('origin');
        $datetime = $request->get('datetime');
        $destination = $request->get('destination');
        $api = empty($request->get('api')) ? 'coinmarketcap.com' : $request->get('api');


        try {
            if(in_array($origin, ['EUR', 'USD', 'CHF'])) {
                $currency_rate = $this->getRate($destination, $origin, $datetime, $api);
                $currency = CryptoCurrency::where('symbol', '=' , $destination)->firstOrFail();

                $rate = $this->coin_to_fiat($amount, $currency_rate);
            } else {
                $currency_rate = $this->getRate($origin, $destination, $datetime, $api);
                $currency = CryptoCurrency::where('symbol', '=' , $origin)->firstOrFail();
                $rate = $this->fiat_to_coin($amount, $currency_rate);
            }

            return [
                'amount' => (string)$rate,
                'currency' => $currency->symbol,
                'api' => $currency_rate->api,
                'last_updated' => date(DATE_ATOM, strtotime($currency_rate->last_updated))
            ];
        } catch (\Exception $e) {
            return new Response('', 400);
        }
    }

    public function GetExchangeHistoryCharts(Request $request) {
        $origin = $request->get('origin');
        $destination = $request->get('destination');
        $start = strtotime($request->get('start_datetime'));
        $end = strtotime($request->get('end_datetime'));
        $api = empty($request->get('api')) ? 'coinmarketcap.com' : $request->get('api');

        try {
            $currency = CryptoCurrency::where('symbol', '=' , $destination)->firstOrFail();

            DB::statement("set session sql_mode=''");

            $currency_rates = $currency->rates()
                ->where('api', $api)
                ->whereBetween('last_updated', [
                    date('Y-m-d H:i:s',$start),
                    date('Y-m-d H:i:s',$end)
                ])
                ->where('currency', $origin)
                ->groupBy(\DB::raw('Date(last_updated)'))
                ->orderBy('last_updated', 'ASC')->get();

            $latest_rate = $currency->rates()
                ->where('api', $api)
                ->where('currency', $origin)
                ->orderBy('last_updated', 'DESC')->firstOrFail();


            try {
                $rate_24hago_end = $currency->rates()
                    ->where('api', $api)
                    ->where('currency', $origin)
                    ->where('last_updated', '>=', date(DATE_ATOM, strtotime('-3 days'))) // optimize
                    ->where('last_updated', '<=', date(DATE_ATOM, strtotime('+3 days'))) // optimize
                    ->where('last_updated', 'LIKE', date('Y-m-d H:%', strtotime('-1day')))
                    ->orderBy('last_updated', 'ASC')->firstOrFail();
            } catch (\Exception $e) {
                $rate_24hago_end = $currency->rates()
                    ->where('api', $api)
                    ->where('currency', $origin)
                    ->where('last_updated', '>=', date(DATE_ATOM, strtotime('-3 days'))) // optimize
                    ->where('last_updated', '<=', date(DATE_ATOM, strtotime('+3 days'))) // optimize
                    ->where('last_updated', 'LIKE', date('Y-m-d H:%', strtotime('-2day')))
                    ->orderBy('last_updated', 'ASC')->firstOrFail();
            }

            $resp_data = [];

            foreach($currency_rates as $currency_rate) {
                $resp_data[] = [strtotime($currency_rate->last_updated), $currency_rate->price];
            }

            return [
                'charts' => $resp_data,
                'latest' => [
                    'price' => $latest_rate->price,
                    'updated' => $latest_rate->last_updated
                ],
                '24hoursago' => [
                    'price' => $rate_24hago_end->price,
                    'updated' => $rate_24hago_end->last_updated
                ]
            ];
        } catch (\Exception $e) {

            return new Response($e->getMessage(), 400);
        }
    }
}
