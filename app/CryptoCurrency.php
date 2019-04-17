<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CryptoCurrency extends Model
{
    protected $table = 'crypto_currency';
    protected $fillable = [ 'name', 'symbol' ];
    public $timestamps = false;

    public function rates()
    {
        return $this->hasMany('App\CryptoCurrencyRate');
    }
}
