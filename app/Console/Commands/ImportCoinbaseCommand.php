<?php
namespace App\Console\Commands;

use App\CryptoCurrency;
use App\CryptoCurrencyRate;
use App\Lib\KrakenAPI;
use App\Lib\ProCoinMarketCapApi;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ImportCoinbaseCommand extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'coinbase:cron';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Imports crypto data from coinbase.com api";
    /**
     * Execute the console command.
     *
     * @return mixed
     */


    /**
     * @var array
     */
    protected $cryptoSymbols;

    /**
     * @var Client
     */
    protected $restApi;

    public function __construct() {
        $configuration = Configuration::apiKey('', '');
        $this->restApi = Client::create($configuration);
        $this->cryptoSymbols = include base_path('config/crypto-symbols.php');
        parent::__construct();
    }

    public function handle() {

        $responses=[];
        $responses[] = $this->restApi->getExchangeRates('BTC');
        $responses[] = $this->restApi->getExchangeRates('LTC');
        $responses[] = $this->restApi->getExchangeRates('ETH');
        $responses[] = $this->restApi->getExchangeRates('BCH');

        foreach($responses as $response) {
            if(!$response) return;

            $this->importCurrency($response['currency'], 'USD', $response['rates']['USD']);
            $this->importCurrency($response['currency'], 'EUR', $response['rates']['EUR']);
        }

    }

    private function importCurrency($origin, $destination, $currencyPrice) {
        $cryptoSymbolName = $this->cryptoSymbols[$origin];
        if(!$cryptoSymbolName) return;

        $cryptoCurrency = CryptoCurrency::firstOrCreate(
            [ 'symbol' => $origin ],
            [ 'name'   => $cryptoSymbolName ]
        );

        $lastUpdated = date('Y-m-d H:i:s', strtotime('now'));
        $api = 'coinbase.com';

        CryptoCurrencyRate::insert(
            [
                'crypto_currency_id' => $cryptoCurrency->id,
                'currency' => $destination,
                'api' => $api,
                'last_updated' => $lastUpdated,
                'price' => $currencyPrice
            ]
        );
    }

}