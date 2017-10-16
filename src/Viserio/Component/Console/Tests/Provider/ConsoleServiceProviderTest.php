<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Console\Tests\Fixture\GoodbyeCommand;
use Viserio\Component\Console\Tests\Fixture\HelloCommand;
use Viserio\Component\Console\Tests\Fixture\LazyWhiner;
use Viserio\Component\Console\Tests\Fixture\SpyOutput;
use Viserio\Component\Container\Container;
use Viserio\Component\Events\Provider\EventsServiceProvider;

class ConsoleServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->instance('config', [
            'viserio' => [
                'console' => [],
            ],
        ]);
        $container->register(new EventsServiceProvider());
        $container->register(new ConsoleServiceProvider());

        $console = $container->get(Application::class);

        self::assertInstanceOf(Application::class, $console);
        self::assertInstanceOf(Application::class, $container->get(SymfonyConsole::class));
        self::assertInstanceOf(Application::class, $container->get('console'));
        self::assertInstanceOf(Application::class, $container->get('cerebro'));
        self::assertInstanceOf(ContainerCommandLoader::class, $container->get(ContainerCommandLoader::class));
        self::assertSame('UNKNOWN', $console->getVersion());
        self::assertSame('UNKNOWN', $console->getName());
    }

    public function testLazilyCommands(): void
    {
        $container = new Container();
        $container->instance('config', [
            'viserio' => [
                'console' => [
                    'lazily_commands' => [
                        'hello'   => HelloCommand::class,
                        'goodbye' => GoodbyeCommand::class,
                    ],
                ],
            ],
        ]);
        $container->singleton(LazyWhiner::class, LazyWhiner::class);
        $container->singleton(HelloCommand::class, HelloCommand::class);
        $container->singleton(GoodbyeCommand::class, GoodbyeCommand::class);
        $container->register(new EventsServiceProvider());
        $container->register(new ConsoleServiceProvider());

        LazyWhiner::setOutput(new SpyOutput());
        $output      = new SpyOutput();
        $application = $container->get(Application::class);

        $application->run(new StringInput('hello'), $output);

        self::assertSame('Hello World!', $output->output);
        self::assertSame('LazyWhiner says:
Viserio\Component\Container\Container woke me up! :-(

LazyWhiner says:
Viserio\Component\Console\Tests\Fixture\HelloCommand made me do work! :-(

', LazyWhiner::getOutput());

        LazyWhiner::setOutput(new SpyOutput());
        $output  = new SpyOutput();
        $application->run(new StringInput('goodbye'), $output);

        self::assertSame('Goodbye World!', $output->output);
        self::assertSame('LazyWhiner says:
Viserio\Component\Container\Container woke me up! :-(

LazyWhiner says:
Viserio\Component\Console\Tests\Fixture\HelloCommand made me do work! :-(

', LazyWhiner::getOutput());
    }
}
