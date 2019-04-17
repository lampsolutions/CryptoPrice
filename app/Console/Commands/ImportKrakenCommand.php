<?php
namespace App\Console\Commands;

use App\CryptoCurrency;
use App\CryptoCurrencyRate;
use App\Lib\KrakenAPI;
use App\Lib\ProCoinMarketCapApi;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ImportKrakenCommand extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'kraken:cron';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Imports crypto data from kraken.com api";
    /**
     * Execute the console command.
     *
     * @return mixed
     */

    /**
     * @var KrakenAPI
     */
    protected $restApi;

    /**
     * @var array
     */
    protected $krakenPairs;

    /**
     * @var array
     */
    protected $cryptoSymbols;

    public function __construct() {
        $this->cryptoSymbols = include base_path('config/crypto-symbols.php');
        $this->krakenPairs = include base_path('config/kraken-pairs.php');
        $this->restApi = new KrakenAPI(env('KRAKEN_API_KEY'), env('KRAKEN_SECRET_KEY'));
        parent::__construct();
    }

    public function handle()
    {
        $queryPairs = array_map(function($e) {
            return $e['queryName'];
        }, $this->krakenPairs);

        $response = $this->restApi->QueryPublic('Ticker', ['pair' => implode(',', $queryPairs) ] );

        if($response && empty($response['error'])) {
            foreach($response['result'] as $pair => $data) {
                $pairInfo = $this->krakenPairs[$pair];
                if(!$pairInfo) continue;

                $cryptoSymbolName = $this->cryptoSymbols[$pairInfo['from']];
                if(!$cryptoSymbolName) continue;

                $cryptoCurrency = CryptoCurrency::firstOrCreate(
                    [ 'symbol' => $pairInfo['from'] ],
                    [ 'name'   => $cryptoSymbolName ]
                );

                $currencyPrice = $data["c"][0];
                $lastUpdated = date('Y-m-d H:i:s', strtotime('now'));
                $api = 'kraken.com';

                CryptoCurrencyRate::insert(
                    [
                        'crypto_currency_id' => $cryptoCurrency->id,
                        'currency' => $pairInfo['to'],
                        'api' => $api,
                        'last_updated' => $lastUpdated,
                        'price' => $currencyPrice
                    ]
                );

            }
        }

    }

}