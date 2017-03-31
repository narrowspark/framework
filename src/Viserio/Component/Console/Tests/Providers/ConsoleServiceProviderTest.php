<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class ConsoleServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new ConsoleServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'console' => [
                    'version' => '1',
                ],
            ],
        ]);

        $console = $container->get(Application::class);

        self::assertInstanceOf(Application::class, $console);
        self::assertSame('1', $console->getVersion());
        self::assertSame('Cerebro', $console->getName());
        self::assertInstanceOf(EventManagerContract::class, $console->getEventManager());
    }
}
