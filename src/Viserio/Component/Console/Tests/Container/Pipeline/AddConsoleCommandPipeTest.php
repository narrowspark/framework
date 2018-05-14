<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Console\Tests\Container\Pipeline;

use SplObjectStorage;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Console\Output\SpyOutput;
use Viserio\Component\Console\Tests\Fixture\GoodbyeCommand;
use Viserio\Component\Console\Tests\Fixture\HelloCommand;
use Viserio\Component\Console\Tests\Fixture\LazyWhiner;
use Viserio\Component\Console\Tests\Fixture\SymfonyCommand;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class AddConsoleCommandPipeTest extends AbstractContainerTestCase
{
    protected const DUMP_CLASS_CONTAINER = false;

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['SHELL_VERBOSITY'], $_GET['SHELL_VERBOSITY'], $_SERVER['SHELL_VERBOSITY']);
    }

    public function testProcess(): void
    {
        $this->containerBuilder->singleton(LazyWhiner::class, LazyWhiner::class);
        $this->containerBuilder->singleton(HelloCommand::class, HelloCommand::class)
            ->addTag('console.command');
        $this->containerBuilder->singleton(GoodbyeCommand::class, GoodbyeCommand::class)
            ->addTag('console.command');

        $this->containerBuilder->register(new ConsoleServiceProvider());

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);

        LazyWhiner::setOutput(new SpyOutput());

        $output = new SpyOutput();
        $application = $this->container->get(Application::class);

        $application->run(new StringInput('hello'), $output);

        self::assertSame('Hello World!', $output->output);
        self::assertSame('LazyWhiner says:
Viserio\Component\Console\Tests\Container\Pipeline\Compiled\AddConsoleCommandPipeContainerTestProcess woke me up! :-(

LazyWhiner says:
Viserio\Component\Console\Tests\Fixture\HelloCommand made me do work! :-(

', LazyWhiner::getOutput());

        LazyWhiner::setOutput(new SpyOutput());

        $output = new SpyOutput();
        $application->run(new StringInput('goodbye'), $output);

        self::assertSame('Goodbye World!', $output->output);
        self::assertSame('LazyWhiner says:
Viserio\Component\Console\Tests\Fixture\GoodbyeCommand made me do work! :-(

', LazyWhiner::getOutput());
    }

    public function testProcessThrowAnExceptionIfTheServiceIsNotASubclassOfCommand(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The service [SplObjectStorage] tagged [console.command] must be a subclass of [Symfony\\Component\\Console\\Command\\Command].');

        $container = $this->containerBuilder;

        $container->getPipelineConfig()->addPipe(new AddConsoleCommandPipe(), PipelineConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->singleton(SplObjectStorage::class)
            ->addTag('console.command');

        $container->compile();
    }

    public function testProcessPrivateServicesWithSameCommand(): void
    {
        $container = $this->containerBuilder;

        $container->singleton('my-command1', SymfonyCommand::class)
            ->addTag('console.command');
        $container->singleton('my-command2', SymfonyCommand::class)
            ->addTag('console.command');

        (new AddConsoleCommandPipe())->process($container);

        $aliasPrefix = 'console.command.public_alias.';

        self::assertTrue($container->hasAlias($aliasPrefix . 'my-command1'));
        self::assertTrue($container->hasAlias($aliasPrefix . 'my-command2'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
