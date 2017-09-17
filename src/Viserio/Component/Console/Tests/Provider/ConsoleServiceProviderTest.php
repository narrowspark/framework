<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Events\Provider\EventsServiceProvider;

class ConsoleServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());
        $container->register(new ConsoleServiceProvider());

        $console = $container->get(Application::class);

        self::assertInstanceOf(Application::class, $console);
        self::assertSame('UNKNOWN', $console->getVersion());
        self::assertSame('UNKNOWN', $console->getName());
        self::assertInstanceOf(EventManagerContract::class, $console->getEventManager());
    }
}
