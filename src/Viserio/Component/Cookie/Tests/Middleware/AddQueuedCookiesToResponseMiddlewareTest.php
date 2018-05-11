<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests\Middleware;

use Narrowspark\TestingHelper\Middleware\RequestHandlerMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Cookie\CookieJar;
use Viserio\Component\Cookie\Middleware\AddQueuedCookiesToResponseMiddleware;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;

class AddQueuedCookiesToResponseMiddlewareTest extends MockeryTestCase
{
    public function tearDown(): void
    {
        unset($_SERVER['SERVER_ADDR']);
    }

    public function testAddQueuedCookiesToResponseMiddleware(): void
    {
        $jar = new CookieJar();
        $jar->queue('test', 'test-v', 4);

        $middleware = new AddQueuedCookiesToResponseMiddleware($jar);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);

        $response = $middleware->process($request, new RequestHandlerMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        $cookies = ResponseCookies::fromResponse($response);

        self::assertSame('test-v', $cookies->get('test')->getValue());
        self::assertSame('test', $cookies->get('test')->getName());
    }
}
