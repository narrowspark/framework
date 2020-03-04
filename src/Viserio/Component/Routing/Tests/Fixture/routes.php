<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;

$router->head('/users', static function () {
    return (new ResponseFactory())
        ->createResponse()
        ->withBody(
            (new StreamFactory())
                ->createStream('all-users')
        );
});
