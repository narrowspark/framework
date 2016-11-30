<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests\Middleware;

use Mockery as Mock;
use DateTime;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cookie\CookieJar;
use Viserio\Cookie\Middleware\AddQueuedCookiesToResponseMiddleware;
use Viserio\Cookie\ResponseCookies;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;

class AddQueuedCookiesToResponseMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
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

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        $cookies = ResponseCookies::fromResponse($response);

        self::assertSame('test-v', $cookies->get('test')->getValue());
        self::assertSame('test', $cookies->get('test')->getName());
    }
}
