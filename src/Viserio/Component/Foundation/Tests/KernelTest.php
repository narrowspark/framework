<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\Events\LocaleChangedEvent;

class KernelTest extends MockeryTestCase
{
    public function testKernelBootstrap()
    {
        $container = new Container();

        $kernel = $this->getKernel($container);

        $kernel->boot();

        $kernel->bootstrapWith([
            LoadEnvironmentVariables::class,
        ]);

        self::assertTrue($kernel->hasBeenBootstrapped());
    }

    public function testSetLocaleSetsLocaleAndFiresLocaleChangedEvent()
    {
        $container = new Container();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('set')
            ->once()
            ->with('viserio.app.locale', 'foo');
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.locale', 'en')
            ->andReturn('foo');

        $container->instance(RepositoryContract::class, $config);

        $trans = $this->mock(TranslationManagerContract::class);
        $trans->shouldReceive('setLocale')
            ->once()
            ->with('foo');

        $container->instance(TranslationManagerContract::class, $trans);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(LocaleChangedEvent::class));

        $container->instance(EventManagerContract::class, $events);

        $kernel = $this->getKernel($container);

        self::assertInstanceOf(KernelContract::class, $kernel->setLocale('foo'));
        self::assertSame('foo', $kernel->getLocale());
    }

    protected function getKernel($container = null)
    {
        return new class($container) extends AbstractKernel {
            public function __construct($container)
            {
                $this->container = $container;
            }

            protected function initializeContainer(): void
            {
            }
        };
    }
}
