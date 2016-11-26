<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests\Middleware;

use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cookie\CookieJar;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\Cookie\Middleware\AddQueuedCookiesToResponse;
use Viserio\HttpFactory\ServerRequestFactory;
use DateTime;

class AddQueuedCookiesToResponseTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testAddQueuedCookiesToResponse()
    {
        $jar = new CookieJar();
        $jar->queue('test', 'test-v', 4);

        $middleware = new AddQueuedCookiesToResponse($jar);

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        $this->assertTrue(is_string($response->getHeader('Cookie')[0]));
    }
}
