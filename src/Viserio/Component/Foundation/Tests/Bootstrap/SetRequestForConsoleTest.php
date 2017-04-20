<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\SetRequestForConsole;
use Viserio\Component\Foundation\Events\BootstrappedEvent;
use Viserio\Component\Foundation\Events\BootstrappingEvent;

class SetRequestForConsoleTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $kernel = new class() extends AbstractKernel {
            protected function registerBaseServiceProviders(): void
            {
            }

            public function bootstrap(): void
            {
            }
        };
        $kernel->setConfigurations([
            'viserio' => [
                'app' => [
                    'env' => 'prod',
                    'url' => 'http://localhost'
                ]
            ]
        ]);

        $container = $kernel->getContainer();

        $serverRequest = $this->mock(ServerRequestInterface::class);

        $request = $this->mock(ServerRequestFactoryInterface::class);
        $request->shouldReceive('createServerRequest')
            ->once()
            ->with('GET', 'http://localhost')
            ->andReturn($serverRequest);
        $container->instance(ServerRequestFactoryInterface::class, $request);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappingEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappedEvent::class));
        $container->instance(EventManagerContract::class, $events);

        $kernel->bootstrapWith([SetRequestForConsole::class]);

        self::assertInstanceOf(ServerRequestInterface::class, $container->get(ServerRequestInterface::class));
    }
}
