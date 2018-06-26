<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Middleware;

use Narrowspark\TestingHelper\Middleware\RequestHandlerMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Contract\View\Factory as FactoryContract;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\View\Middleware\ShareErrorsFromSessionMiddleware;

/**
 * @internal
 */
final class ShareErrorsFromSessionMiddlewareTest extends MockeryTestCase
{
    public function testProcess(): void
    {
        $session = $this->mock(StoreContract::class);
        $session->shouldReceive('get')
            ->once()
            ->with('errors', [])
            ->andReturn([]);

        $view = $this->mock(FactoryContract::class);
        $view->shouldReceive('share')
            ->once()
            ->with('errors', []);

        $middleware = new ShareErrorsFromSessionMiddleware($view);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);
        $request = $request->withAttribute('session', $session);

        $middleware->process($request, new RequestHandlerMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));
    }
}
