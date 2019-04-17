<?php

$router->get('/', function () use ($router) {
    return redirect(route('swagger-lume.api'));
});

$router->group(['prefix' => 'api/v1/'], function () use ($router) {
    $router->get('calculate-exchange/', 'CurrencyController@CalculateExchange');
    $router->post('calculate-exchange/', 'CurrencyController@CalculateExchange');

    $router->get('exchange-history-charts/', 'CurrencyController@GetExchangeHistoryCharts');
    $router->post('exchange-history-charts/', 'CurrencyController@GetExchangeHistoryCharts');
});