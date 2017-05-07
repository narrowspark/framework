<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\DataCollectors;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Foundation\DataCollectors\ViserioHttpDataCollector;

class ViserioHttpDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition()
    {
        $serverRequest = $this->mock(ServerRequestInterface::class);
        $serverRequest->shouldReceive('getAttributes')
            ->once()
            ->withNoArgs()
            ->andReturn([]);

        $response = $this->mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->once()
            ->withNoArgs()
            ->andReturn(200);

        $route = $this->mock(RouteContract::class);
        $route->shouldReceive('getName')
            ->twice()
            ->withNoArgs()
            ->andReturn('Home');

        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getCurrentRoute')
            ->once()
            ->withNoArgs()
            ->andReturn($route);

        $collect = new ViserioHttpDataCollector($router, $this->mock(RepositoryContract::class));
        $collect->collect($serverRequest, $response);

        self::assertSame(
            [
                'label' => '@',
                'value' => 'Home',
            ],
            $collect->getMenu()
        );

        self::assertSame('left', $collect->getMenuPosition());
    }
}
