<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class CryptoCurrencyRate extends Model
{
    protected $table = 'crypto_currency_rate';
    protected $fillable = [ 'crypto_currency_id', 'api', 'last_updated', 'price', 'currency' ];
    public $timestamps = false;
}
