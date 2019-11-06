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

namespace Viserio\Component\View\Tests\Middleware;

use Mockery;
use Narrowspark\TestingHelper\Middleware\RequestHandlerMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\View\Middleware\ShareErrorsFromSessionMiddleware;
use Viserio\Contract\Session\Store as StoreContract;
use Viserio\Contract\View\Factory as FactoryContract;

/**
 * @internal
 *
 * @small
 */
final class ShareErrorsFromSessionMiddlewareTest extends MockeryTestCase
{
    public function testProcess(): void
    {
        $session = Mockery::mock(StoreContract::class);
        $session->shouldReceive('get')
            ->once()
            ->with('errors', [])
            ->andReturn([]);

        $view = Mockery::mock(FactoryContract::class);
        $view->shouldReceive('share')
            ->once()
            ->with('errors', []);

        $middleware = new ShareErrorsFromSessionMiddleware($view);

        $request = new ServerRequest('/');
        $request = $request->withAttribute('session', $session);

        $middleware->process($request, new RequestHandlerMiddleware(static function () {
            return (new ResponseFactory())->createResponse(200);
        }));
    }
}
