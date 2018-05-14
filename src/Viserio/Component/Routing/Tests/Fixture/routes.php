<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
