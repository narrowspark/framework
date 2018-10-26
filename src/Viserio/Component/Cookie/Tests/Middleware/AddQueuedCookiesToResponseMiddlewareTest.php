<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests\Middleware;

use Narrowspark\TestingHelper\Middleware\RequestHandlerMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Cookie\CookieJar;
use Viserio\Component\Cookie\Middleware\AddQueuedCookiesToResponseMiddleware;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class AddQueuedCookiesToResponseMiddlewareTest extends MockeryTestCase
{
    public function testAddQueuedCookiesToResponseMiddleware(): void
    {
        $jar = new CookieJar();
        $jar->queue('test', 'test-v', 4);

        $middleware = new AddQueuedCookiesToResponseMiddleware($jar);

        $response = $middleware->process(new ServerRequest('/'), new RequestHandlerMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        $cookies = ResponseCookies::fromResponse($response);

        $this->assertSame('test-v', $cookies->get('test')->getValue());
        $this->assertSame('test', $cookies->get('test')->getName());
    }
}
