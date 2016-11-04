<?php
declare(strict_types=1);

use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\StreamFactory;

$router->head('/users', function () {
    return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('all-users')
                );
});
