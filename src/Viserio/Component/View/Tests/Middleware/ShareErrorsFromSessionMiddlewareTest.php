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
 * @coversNothing
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
