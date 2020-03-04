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
 *
 * @small
 * @coversNothing
 */
final class AddQueuedCookiesToResponseMiddlewareTest extends MockeryTestCase
{
    public function testAddQueuedCookiesToResponseMiddleware(): void
    {
        $jar = new CookieJar();
        $jar->queue('test', 'test-v', 4);

        $middleware = new AddQueuedCookiesToResponseMiddleware($jar);

        $response = $middleware->process(new ServerRequest('/'), new RequestHandlerMiddleware(static function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        $cookies = ResponseCookies::fromResponse($response);

        self::assertSame('test-v', $cookies->get('test')->getValue());
        self::assertSame('test', $cookies->get('test')->getName());
    }
}
