# CryptoPrice Api

CryptoPrice Api allows you to query cached currency exchange rates from the following digital currency markets:
 
* [coinmarketcap.com](https://coinmarketcap.com/)
* [kraken.com](https://www.kraken.com/) 
* [coinbase.com](https://www.coinbase.com/)

## Example Testsetup

1. Request api keys from [coinmarketcap.com](https://coinmarketcap.com/) and [kraken.com](https://www.kraken.com/).
2. Copy .env.example file to .env and setup the following entries
    * ```DB_PASSWORD:``` random secure password
    * ```COINMARKETCAP_TOKEN:``` Your Coinmarketcap.com API Token
    * ```KRAKEN_API_KEY:``` Your Kraken.com Api Token
    * ```KRAKEN_SECRET_KEY:``` Your Kraken.com Secret Key
3. Run ```docker-compose -f docker-compose-example.yml up``` to start and build your containers stack
4. Every 30 minutes exchange rates are queried and refreshed via cronjobs, to prefill your database use the following commands:
    * Obtain container id by querying docker via ```docker ps``` and look for the image name ```lampsolutions/cryptoprice:0.1```
    * Run ```docker exec <container id> /usr/bin/php /app/artisan coinmarketcap:cron```
    * Run ```docker exec <container id> /usr/bin/php /app/artisan coinbase:cron```
    * Run ```docker exec <container id> /usr/bin/php /app/artisan kraken:cron```   
5. You should now be able to query the api via ```http://localhost:8080/api/v1/calculate-exchange?amount=333&origin=USD&destination=BTC&api=coinmarketcap.com```

## Documentation and Usage examples

To see an example api, please visit [cryptoprice.api.cryptopanel.de](https://cryptoprice.api.cryptopanel.de/api/documentation).

You can also visit our german website [www.cryptopanel.de](https://www.cryptopanel.de).

## License
Code released under [the MIT license](https://github.com/lamp-aw/docker-ltc-litecore/blob/master/LICENSE).