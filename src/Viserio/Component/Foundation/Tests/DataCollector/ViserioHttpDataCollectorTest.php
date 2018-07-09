<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Routing\Route as RouteContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Foundation\DataCollector\ViserioHttpDataCollector;

/**
 * @internal
 */
final class ViserioHttpDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition(): void
    {
        $serverRequest = $this->mock(ServerRequestInterface::class);
        $serverRequest->shouldReceive('getAttributes')
            ->once()
            ->andReturn([]);

        $response = $this->mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);

        $route = $this->mock(RouteContract::class);
        $route->shouldReceive('getName')
            ->twice()
            ->andReturn('Home');

        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getCurrentRoute')
            ->once()
            ->andReturn($route);

        $collect = new ViserioHttpDataCollector($router, '');
        $collect->collect($serverRequest, $response);

        static::assertSame(
            [
                'label' => '@',
                'class' => 'response-status-green',
                'value' => 'Home',
            ],
            $collect->getMenu()
        );

        static::assertSame('left', $collect->getMenuPosition());
    }
}
