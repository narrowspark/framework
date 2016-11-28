<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests\Middleware;

use DateTime;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cookie\CookieJar;
use Viserio\Cookie\Middleware\AddQueuedCookiesToResponseMiddleware;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;

class AddQueuedCookiesToResponseMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testAddQueuedCookiesToResponseMiddleware()
    {
        $jar = new CookieJar();
        $jar->queue('test', 'test-v', 4);

        $middleware = new AddQueuedCookiesToResponseMiddleware($jar);

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        self::assertTrue(is_string($response->getHeader('Set-Cookie')[0]));
    }
}
