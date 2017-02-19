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
use Viserio\Component\Contracts\Console\Kernel as KernelContract;
use Viserio\Component\Contracts\Console\Terminable as TerminableContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Cron\Providers\CronServiceProvider;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Foundation\Bootstrap\DetectEnvironment;
use Viserio\Component\Foundation\Bootstrap\HandleExceptions;
use Viserio\Component\Foundation\Bootstrap\LoadConfiguration;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;

class Kernel implements KernelContract, TerminableContract
{
    use EventsAwareTrait;

    /**
     * The application implementation.
     *
     * @var \Viserio\Component\Contracts\Foundation\Application
     */
    protected $app;

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
        LoadConfiguration::class,
        DetectEnvironment::class,
        HandleExceptions::class,
        LoadServiceProvider::class,
    ];

    /**
     * Create a new console kernel instance.
     *
     * @param \Viserio\Component\Contracts\Foundation\Application $app
     * @param \Viserio\Component\Contracts\Events\EventManager    $events
     */
    public function __construct(
        ApplicationContract $app,
        EventManagerContract $events
    ) {
        $this->app    = $app;
        $this->events = $events;
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
                $this->commands();
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
    public function terminate(InputInterface $input, int $status)
    {
        $this->app->get(HandlerContract::class)->unregister();
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
     * Set the console application instance.
     *
     * @param \Viserio\Component\Console\Application $console
     */
    public function setConsole(Cerebro $console)
    {
        $this->console = $console;
    }

    /**
     * Bootstrap the application for console commands.
     */
    public function bootstrap()
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers);
        }
    }

    /**
     * Register the given command with the console application.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     */
    public function registerCommand(SymfonyCommand $command)
    {
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
    public function command(string $signature, Closure $callback)
    {
        $command = new ClosureCommand($signature, $callback);

        Cerebro::starting(function ($console) use ($command) {
            $console->add($command);
        });

        return $command;
    }

    /**
     * Define the application's command schedule.
     */
    protected function defineConsoleSchedule()
    {
        if (class_exists(CronServiceProvider::class)) {
            $this->app->register(new CronServiceProvider());

            $this->schedule($this->app->get(Schedule::class));
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return array
     */
    protected function commands(): array
    {
        return [];
    }

    /**
     * Get the cerebro application instance.
     *
     * @return \Viserio\Component\Console\Application
     */
    protected function getConsole(): Cerebro
    {
        if (is_null($this->console)) {
            $console = $this->app->get(Cerebro::class);

            foreach ($this->commands as $command) {
                $console->add($this->app->make($command));
            }

            return $this->console = $console;
        }

        return $this->console;
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers(): array
    {
        return $this->bootstrappers;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Throwable $exception
     */
    protected function reportException(Throwable $exception)
    {
        $this->app->get(HandlerContract::class)->report($exception);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     * @param \Throwable                                             $exception
     */
    protected function renderException($output, Throwable $exception)
    {
        $this->getConsole()->renderException($exception, $output);
    }

    /**
     * Define the application's command schedule.
     *
     * @param \Viserio\Component\Cron\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
    }
}
