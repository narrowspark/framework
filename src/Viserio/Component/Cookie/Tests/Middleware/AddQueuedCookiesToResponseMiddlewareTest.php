<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests\Middleware;

use Mockery as Mock;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Cookie\CookieJar;
use Viserio\Component\Cookie\Middleware\AddQueuedCookiesToResponseMiddleware;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;

class AddQueuedCookiesToResponseMiddlewareTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        unset($_SERVER['SERVER_ADDR']);

        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testAddQueuedCookiesToResponseMiddleware()
    {
        $jar = new CookieJar();
        $jar->queue('test', 'test-v', 4);

        $middleware = new AddQueuedCookiesToResponseMiddleware($jar);

        $server = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequest($server);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        $cookies = ResponseCookies::fromResponse($response);

        self::assertSame('test-v', $cookies->get('test')->getValue());
        self::assertSame('test', $cookies->get('test')->getName());
    }
}
