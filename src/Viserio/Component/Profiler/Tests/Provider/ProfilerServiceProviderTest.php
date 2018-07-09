<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\Profiler;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
use Viserio\Component\Routing\Provider\RoutingServiceProvider;

/**
 * @internal
 */
final class ProfilerServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new EventsServiceProvider());

        $container->register(new ProfilerServiceProvider());

        $container->instance('config', ['viserio' => ['profiler' => ['enable' => true]]]);

        static::assertInstanceOf(AssetsRenderer::class, $container->get(AssetsRenderer::class));
        static::assertInstanceOf(Profiler::class, $container->get(ProfilerContract::class));
        static::assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
    }

    public function testRouteGroups(): void
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new RoutingServiceProvider());
        $container->register(new ProfilerServiceProvider());

        $container->instance('config', ['viserio' => ['profiler' => ['enable' => true]]]);

        $router = $container->get(RouterContract::class);
        $routes = $router->getRoutes()->getRoutes();

        $action1 = $routes[0]->getAction();
        $action2 = $routes[1]->getAction();

        static::assertEquals('Viserio\Component\Profiler\Controller\AssetController@css', $action1['controller']);
        static::assertEquals('Viserio\Component\Profiler\Controller\AssetController@js', $action2['controller']);
    }

    private function getRequest()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->with('request_time_float')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('request_time')
            ->andReturn(false);

        return $request;
    }
}
