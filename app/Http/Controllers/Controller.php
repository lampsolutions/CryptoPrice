<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     description="CryptoPrice API
[https://www.cryptopanel.de/].",
 *     version="1.0.0",
 *     title="CryptoPrice API",
 *     termsOfService="",
 *     @OA\Contact(
 *         email="support@cryptopanel.de"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="http://opensource.org/licenses/MIT"
 *     )
 * )
 */
/**
 * @OA\Tag(
 *     name="exchange",
 *     description="Calculate exchange between currencies",
 *     @OA\ExternalDocumentation(
 *         description="Find out more",
 *         url="https://www.cryptopanel.de/"
 *     )
 * )
 * @OA\Server(
 *     description="CryptoPrice API Endpoint",
 *     url="https://cryptoprice.api.cryptopanel.de"
 * )
 */
class Controller extends BaseController
{
    //
}
