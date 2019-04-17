<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangeRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_currency', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('symbol')->unique();
        });


        Schema::create('crypto_currency_rate', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('crypto_currency_id');
            $table->string('currency');
            $table->enum('api', [ 'coinmarketcap.com', 'kraken.com', 'coinbase.com'] );
            $table->dateTime('last_updated');
            $table->decimal('price', 32, 8);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crypto_currency');
        Schema::dropIfExists('crypto_currency_rate');
    }
}
