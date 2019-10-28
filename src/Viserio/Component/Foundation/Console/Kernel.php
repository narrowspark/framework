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

namespace Viserio\Component\Foundation\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Viserio\Component\Console\Application as Cerebro;
use Viserio\Component\Exception\Console\SymfonyConsoleOutput;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Contract\Console\Terminable as TerminableContract;
use Viserio\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;

/**
 * @TODO add all public application methods
 *
 * @mixin \Viserio\Component\Console\Application
 *
 * @method int call(string $command, array $parameters = [], ?\Symfony\Component\Console\Output\OutputInterface $outputBuffer = null)
 */
class Kernel extends AbstractKernel implements ConsoleKernelContract, TerminableContract
{
    /**
     * The cerebro application instance.
     *
     * @var \Viserio\Component\Console\Application
     */
    protected $console;

    /**
     * List of allowed bootstrap types.
     *
     * @internal
     *
     * @var array
     */
    protected static $allowedBootstrapTypes = ['global', 'console'];

    /**
     * Create a new console kernel instance.
     */
    public function __construct()
    {
        if (! \defined('CEREBRO_BINARY')) {
            \define('CEREBRO_BINARY', 'cerebro');
        }

        parent::__construct();
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        $this->bootstrap();

        return $this->getConsole()->{$method}(...$arguments);
    }

    /**
     * Get the cerebro application instance.
     *
     * @return \Viserio\Component\Console\Application
     */
    protected function getConsole(): Cerebro
    {
        if ($this->console === null) {
            $console = $this->getContainer()->get(Cerebro::class);

            $console->setVersion($this->resolvedOptions['version']);
            $console->setName($this->resolvedOptions['console_name']);

            return $this->console = $console;
        }

        return $this->console;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        $options = [
            'url' => 'http://localhost',
            'version' => self::VERSION,
            'console_name' => 'Cerebro',
        ];

        return \array_merge(parent::getDefaultOptions(), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(InputInterface $input, OutputInterface $output = null): int
    {
        $this->bootstrap();

        try {
            return $this->getConsole()->run($input, $output);
        } catch (Throwable $exception) {
            $this->reportException($exception);
            $this->renderException($output, $exception);

            return 1;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(InputInterface $input, int $status): void
    {
        if (! $this->bootstrapManager->hasBeenBootstrapped()) {
            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        $this->bootstrap();

        return $this->getConsole()->all();
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function getOutput(): string
    {
        $this->bootstrap();

        return $this->getConsole()->getLastOutput();
    }

    /**
     * Register the given command with the console application.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return void
     */
    public function registerCommand(SymfonyCommand $command): void
    {
        $this->bootstrap();

        $this->getConsole()->add($command);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    protected function reportException(Throwable $exception): void
    {
        $container = $this->getContainer();

        if ($container->has(ConsoleHandlerContract::class)) {
            $container->get(ConsoleHandlerContract::class)->report($exception);
        }
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param null|\Symfony\Component\Console\Output\OutputInterface $output
     * @param \Throwable                                             $exception
     *
     * @return void
     */
    protected function renderException($output, Throwable $exception): void
    {
        if ($output instanceof ConsoleOutput) {
            $output = $output->getErrorOutput();
        }

        $container = $this->getContainer();

        if ($container->has(ConsoleHandlerContract::class)) {
            $container->get(ConsoleHandlerContract::class)
                ->render(new SymfonyConsoleOutput($output), $exception);
        } else {
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getBootstrapLockFileName(): string
    {
        return 'console_bootstrap.lock';
    }
}
