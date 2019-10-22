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

namespace Viserio\Component\Routing\Tests\Fixture;

use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\AbstractController;

class RouteTestClosureMiddlewareController extends AbstractController
{
    public function __construct()
    {
        $this->withMiddleware(ControllerClosureMiddleware::class);
    }

    public function index()
    {
        return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                    ->createStream('index')
            );
    }
}
