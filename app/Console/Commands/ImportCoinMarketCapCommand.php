<?php
namespace App\Console\Commands;

use App\CryptoCurrency;
use App\CryptoCurrencyRate;
use App\Lib\ProCoinMarketCapApi;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ImportCoinMarketCapCommand extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'coinmarketcap:cron';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Imports crypto data from coinmarketcap.com api";
    /**
     * Execute the console command.
     *
     * @return mixed
     */

    /**
     * @var ProCoinMarketCapApi
     */
    protected $restApi;

    /**
     * @var array
     */
    protected $symbols;

    /**
     * @var array
     */
    protected $currencies;

    public function __construct() {

        $this->symbols      = explode(',', env('COINMARKETCAP_CURRENCY_SYMBOLS'));
        $this->currencies   = explode(',',env('COINMARKETCAP_CURRENCIES_RATE_LIST'));

        $this->restApi = new ProCoinMarketCapApi(env('COINMARKETCAP_TOKEN'));
        parent::__construct();
    }

    public function handle()
    {
        foreach($this->currencies as $currency) {
            $response = $this->restApi->getMarketQuotes([], $this->symbols, [ $currency ]);

            if($response->status->error_code > 0) continue;

            foreach($response->data as $data) {

                $cryptoCurrency = CryptoCurrency::firstOrCreate(
                    [ 'symbol' => $data->symbol ],
                    [ 'name'   => $data->name ]
                );

                $currencyPrice = $data->quote->$currency->price;
                $lastUpdated = date('Y-m-d H:i:s', strtotime($data->quote->$currency->last_updated));
                $api = 'coinmarketcap.com';

                CryptoCurrencyRate::insert(
                    [
                        'crypto_currency_id' => $cryptoCurrency->id,
                        'currency' => $currency,
                        'api' => $api,
                        'last_updated' => $lastUpdated,
                        'price' => $currencyPrice
                    ]
                );

            }
        }
    }

}