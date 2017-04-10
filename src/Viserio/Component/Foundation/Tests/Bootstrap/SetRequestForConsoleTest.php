<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Mockery as Mock;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Foundation\Application;
use Viserio\Component\Foundation\Bootstrap\SetRequestForConsole;
use Viserio\Component\Foundation\Events\BootstrappingEvent;
use Viserio\Component\Foundation\Events\BootstrappedEvent;

class SetRequestForConsoleTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $app = new class() extends Application {
            public function __construct()
            {
            }

            /**
             * Register all of the base service providers.
             *
             * @return void
             */
            protected function registerBaseServiceProviders(): void
            {
            }
        };

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('app.url', 'http://localhost')
            ->andReturn('http://localhost');
        $app->instance(RepositoryContract::class, $config);

        $serverRequest = $this->mock(ServerRequestInterface::class);

        $request = $this->mock(ServerRequestFactoryInterface::class);
        $request->shouldReceive('createServerRequest')
            ->once()
            ->with('GET', 'http://localhost')
            ->andReturn($serverRequest);
        $app->instance(ServerRequestFactoryInterface::class, $request);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappingEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappedEvent::class));
        $app->instance(EventManagerContract::class, $events);

        $app->bootstrapWith([SetRequestForConsole::class]);

        self::assertInstanceOf(ServerRequestInterface::class, $app->get(ServerRequestInterface::class));
    }
}
