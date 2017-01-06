<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Events\Providers\EventsServiceProvider;
use Viserio\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Routing\Providers\RoutingServiceProvider;
use Viserio\WebProfiler\AssetsRenderer;
use Viserio\WebProfiler\Providers\WebProfilerServiceProvider;
use Viserio\WebProfiler\WebProfiler;

class WebProfilerServiceProviderTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testProvider()
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new ConfigServiceProvider());
        $container->register(new WebProfilerServiceProvider());

        self::assertInstanceOf(AssetsRenderer::class, $container->get(AssetsRenderer::class));
        self::assertInstanceOf(WebProfiler::class, $container->get(WebProfiler::class));
        self::assertInstanceOf(WebProfilerContract::class, $container->get(WebProfilerContract::class));
    }

    public function testRouteGroups()
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new RoutingServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new WebProfilerServiceProvider());

        $router  = $container->get(RouterContract::class);
        $routes  = $router->getRoutes()->getRoutes();
        $action1 = $routes[0]->getAction();
        $action2 = $routes[1]->getAction();

        self::assertEquals('Viserio\WebProfiler\Controllers\AssetController@css', $action1['controller']);
        self::assertEquals('Viserio\WebProfiler\Controllers\AssetController@js', $action2['controller']);
    }

    private function getRequest()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->with('REQUEST_TIME_FLOAT')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('REQUEST_TIME')
            ->andReturn(false);

        return $request;
    }
}
