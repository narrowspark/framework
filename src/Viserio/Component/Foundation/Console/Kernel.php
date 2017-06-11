<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console;

use Closure;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;
use Viserio\Component\Console\Application as Cerebro;
use Viserio\Component\Console\Command\ClosureCommand;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Contracts\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Contracts\Console\Terminable as TerminableContract;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Cron\Providers\CronServiceProvider;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;
use Viserio\Component\Foundation\Bootstrap\HandleExceptions;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\BootstrapManager;

class Kernel extends AbstractKernel implements ConsoleKernelContract, TerminableContract
{
    /**
     * The cerebro application instance.
     *
     * @var \Viserio\Component\Console\Application
     */
    protected $console;

    /**
     * The Cerebro commands provided by the application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Indicates if the Closure commands have been loaded.
     *
     * @var bool
     */
    protected $commandsLoaded = false;

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        ConfigureKernel::class,
        LoadEnvironmentVariables::class,
        HandleExceptions::class,
    ];

    /**
     * Create a new console kernel instance.
     */
    public function __construct()
    {
        if (! defined('CEREBRO_BINARY')) {
            define('CEREBRO_BINARY', 'cerebro');
        }

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        $options = [
            'url'          => 'http://localhost',
            'version'      => self::VERSION,
            'console_name' => 'Cerebro',
        ];

        return array_merge(parent::getDefaultOptions(), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(InputInterface $input, OutputInterface $output = null): int
    {
        try {
            $this->bootstrap();

            $this->defineConsoleSchedule();

            if (! $this->commandsLoaded) {
                $this->getCommands();
                $this->commandsLoaded = true;
            }

            return $this->getConsole()->run($input, $output);
        } catch (Throwable $exception) {
            $exception = new FatalThrowableError($exception);

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
        if (! $this->getContainer()->get(BootstrapManager::class)->hasBeenBootstrapped()) {
            return;
        }

        restore_error_handler();
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

        return $this->getConsole()->output();
    }

    /**
     * Bootstrap the kernel for console commands.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        $bootstrapManager = $this->getContainer()->get(BootstrapManager::class);

        if (! $bootstrapManager->hasBeenBootstrapped()) {
            $bootstrapManager->bootstrapWith($this->bootstrappers);
        }
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
     * Register a Closure based command with the application.
     *
     * @param string   $signature
     * @param \Closure $callback
     *
     * @return \Viserio\Component\Console\Command\ClosureCommand
     */
    public function command(string $signature, Closure $callback): ClosureCommand
    {
        $command = new ClosureCommand($signature, $callback);

        Cerebro::starting(function ($console) use ($command) {
            $console->add($command);
        });

        return $command;
    }

    /**
     * Run an console command by name.
     *
     * @param string                                                 $command
     * @param array                                                  $parameters
     * @param null|\Symfony\Component\Console\Output\OutputInterface $outputBuffer
     *
     * @return int
     */
    public function call(string $command, array $parameters = [], ?OutputInterface $outputBuffer = null): int
    {
        $this->bootstrap();

        if (! $this->commandsLoaded) {
            $this->getCommands();

            $this->commandsLoaded = true;
        }

        return $this->getConsole()->call($command, $parameters, $outputBuffer);
    }

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function defineConsoleSchedule(): void
    {
        if (class_exists(CronServiceProvider::class)) {
            $container = $this->getContainer();

            $container->register(new CronServiceProvider());

            $this->getSchedule($container->get(Schedule::class));
        }
    }

    /**
     * Get the cerebro application instance.
     *
     * @return \Viserio\Component\Console\Application
     */
    protected function getConsole(): Cerebro
    {
        if ($this->console === null) {
            $container = $this->getContainer();
            $console   = $container->get(Cerebro::class);

            $console->setVersion($this->resolvedOptions['version']);
            $console->setName($this->resolvedOptions['name']);

            foreach ($this->commands as $command) {
                $console->add($container->resolve($command));
            }

            return $this->console = $console;
        }

        return $this->console;
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
        $this->getContainer()->get(ExceptionHandlerContract::class)->report($exception);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     * @param \Throwable                                             $exception
     *
     * @return void
     */
    protected function renderException($output, Throwable $exception): void
    {
        $this->getContainer()->get(ExceptionHandlerContract::class)->renderForConsole($output, $exception);
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders(): void
    {
        parent::registerBaseServiceProviders();

        $this->getContainer()->register(new ConsoleServiceProvider());
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings(): void
    {
        parent::registerBaseBindings();

        $kernel    = $this;
        $container = $this->getContainer();

        $container->singleton(ConsoleKernelContract::class, function () use ($kernel) {
            return $kernel;
        });

        $container->alias(ConsoleKernelContract::class, self::class);
        $container->alias(ConsoleKernelContract::class, 'console_kernel');
        $container->alias(Cerebro::class, self::class);
    }

    /**
     * Define the application's command schedule.
     *
     * @param \Viserio\Component\Cron\Schedule $schedule
     *
     * @return void
     */
    protected function getSchedule(Schedule $schedule): void
    {
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function getCommands(): void
    {
    }
}
