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

        $this->assertInstanceOf(Application::class, $console);
        $this->assertInstanceOf(Application::class, $container->get(SymfonyConsole::class));
        $this->assertInstanceOf(Application::class, $container->get('console'));
        $this->assertInstanceOf(Application::class, $container->get('cerebro'));
        $this->assertSame('UNKNOWN', $console->getVersion());
        $this->assertSame('UNKNOWN', $console->getName());
    }
}
