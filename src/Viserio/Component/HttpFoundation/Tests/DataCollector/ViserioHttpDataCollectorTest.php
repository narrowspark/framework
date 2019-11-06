<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\HttpFoundation\Tests\DataCollector;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFoundation\DataCollector\ViserioHttpDataCollector;
use Viserio\Contract\Routing\Route as RouteContract;
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 *
 * @small
 */
final class ViserioHttpDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition(): void
    {
        $serverRequest = Mockery::mock(ServerRequestInterface::class);
        $serverRequest->shouldReceive('getAttributes')
            ->once()
            ->andReturn([]);

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);

        $route = Mockery::mock(RouteContract::class);
        $route->shouldReceive('getName')
            ->twice()
            ->andReturn('Home');

        $router = Mockery::mock(RouterContract::class);
        $router->shouldReceive('getCurrentRoute')
            ->once()
            ->andReturn($route);

        $collect = new ViserioHttpDataCollector($router, '');
        $collect->collect($serverRequest, $response);

        self::assertSame(
            [
                'label' => '@',
                'class' => 'response-status-green',
                'value' => 'Home',
            ],
            $collect->getMenu()
        );

        self::assertSame('left', $collect->getMenuPosition());
    }
}
