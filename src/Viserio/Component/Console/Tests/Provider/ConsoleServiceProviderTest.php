<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application as SymfonyConsole;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Events\Provider\EventsServiceProvider;

/**
 * @internal
 */
final class ConsoleServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());
        $container->register(new ConsoleServiceProvider());

        $console = $container->get(Application::class);

        static::assertInstanceOf(Application::class, $console);
        static::assertInstanceOf(Application::class, $container->get(SymfonyConsole::class));
        static::assertInstanceOf(Application::class, $container->get('console'));
        static::assertInstanceOf(Application::class, $container->get('cerebro'));
        static::assertSame('UNKNOWN', $console->getVersion());
        static::assertSame('UNKNOWN', $console->getName());
    }
}
