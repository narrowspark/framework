<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Output\SpyOutput;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Console\Provider\LazilyCommandsServiceProvider;
use Viserio\Component\Console\Tests\Fixture\GoodbyeCommand;
use Viserio\Component\Console\Tests\Fixture\HelloCommand;
use Viserio\Component\Console\Tests\Fixture\LazyWhiner;
use Viserio\Component\Container\Container;

/**
 * @internal
 */
final class LazilyCommandsServiceProviderTest extends TestCase
{
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
        $container->register(new ConsoleServiceProvider());
        $container->register(new LazilyCommandsServiceProvider());

        LazyWhiner::setOutput(new SpyOutput());
        $output      = new SpyOutput();
        $application = $container->get(Application::class);

        $application->run(new StringInput('hello'), $output);

        static::assertSame('Hello World!', $output->output);
        static::assertSame('LazyWhiner says:
Viserio\Component\Container\Container woke me up! :-(

LazyWhiner says:
Viserio\Component\Console\Tests\Fixture\HelloCommand made me do work! :-(

', LazyWhiner::getOutput());

        LazyWhiner::setOutput(new SpyOutput());
        $output = new SpyOutput();
        $application->run(new StringInput('goodbye'), $output);

        static::assertSame('Goodbye World!', $output->output);
        static::assertSame('LazyWhiner says:
Viserio\Component\Console\Tests\Fixture\GoodbyeCommand made me do work! :-(

', LazyWhiner::getOutput());
    }
}
