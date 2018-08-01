<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Middleware;

use Narrowspark\TestingHelper\Middleware\RequestHandlerMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Contract\View\Factory as FactoryContract;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\HttpFactory\ResponseFactory;
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

        $request = new ServerRequest('/');
        $request = $request->withAttribute('session', $session);

        $middleware->process($request, new RequestHandlerMiddleware(function () {
            return (new ResponseFactory())->createResponse(200);
        }));
    }
}
