<?php
namespace App\Console\Commands;

use App\CryptoCurrency;
use App\CryptoCurrencyRate;
use App\Lib\KrakenAPI;
use App\Lib\ProCoinMarketCapApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;

class DockerTryCreateDB extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'docker:createdb';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Tries to create mysql db before running migrations";
    /**
     * Execute the console command.
     *
     * @return mixed
     */


    public function __construct() {
        parent::__construct();
    }

    public function handle()
    {
        DB::statement('CREATE DATABASE IF NOT EXISTS '.env('DB_DATABASE'));
    }

}