<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;
use Viserio\Component\WebProfiler\AssetsRenderer;
use Viserio\Component\WebProfiler\Providers\WebProfilerServiceProvider;
use Viserio\Component\WebProfiler\WebProfiler;

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
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new WebProfilerServiceProvider());

        $container->instance('config', ['viserio' => ['webprofiler' => ['enable' => true]]]);

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
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new WebProfilerServiceProvider());

        $container->instance('config', ['viserio' => ['webprofiler' => ['enable' => true]]]);

        $router  = $container->get(RouterContract::class);
        $routes  = $router->getRoutes()->getRoutes();
        $action1 = $routes[0]->getAction();
        $action2 = $routes[1]->getAction();

        self::assertEquals('Viserio\Component\WebProfiler\Controllers\AssetController@css', $action1['controller']);
        self::assertEquals('Viserio\Component\WebProfiler\Controllers\AssetController@js', $action2['controller']);
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
