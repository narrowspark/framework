<?php
declare(strict_types=1);
namespace Viserio\Component\Console;

use Closure;
use ErrorException;
use Exception;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputAwareInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Process\PhpExecutableFinder;
use Throwable;
use Viserio\Component\Console\Command\Command as ViserioCommand;
use Viserio\Component\Console\Command\CommandResolver;
use Viserio\Component\Console\Command\StringCommand;
use Viserio\Component\Console\Event\ConsoleCommandEvent;
use Viserio\Component\Console\Event\ConsoleErrorEvent;
use Viserio\Component\Console\Event\ConsoleTerminateEvent;
use Viserio\Component\Console\Input\InputOption;
use Viserio\Component\Contract\Console\Exception\LogicException;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Support\Invoker;

class Application extends SymfonyConsole
{
    use ContainerAwareTrait;
    use EventManagerAwareTrait;

    /**
     * The console application bootstrappers.
     *
     * @var array
     */
    protected static $bootstrappers = [];

    /**
     * Console name.
     *
     * @var string
     */
    protected $name;

    /**
     * Console version.
     *
     * @var string
     */
    protected $version;

    /**
     * The running command.
     *
     * @var null|\Symfony\Component\Console\Command\Command
     */
    private $runningCommand;

    /**
     * The output from the previous command.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $lastOutput;

    /**
     * Invoker instance.
     *
     * @var \Viserio\Component\Support\Invoker
     */
    private $invoker;

    /**
     * Symfony terminal instance.
     *
     * @var \Symfony\Component\Console\Terminal
     */
    private $terminal;

    /**
     * Create a new cerebro console application.
     *
     * @param string $version The version of the application
     * @param string $name    The name of the application
     */
    public function __construct(string $version = 'UNKNOWN', string $name = 'UNKNOWN')
    {
        $this->name     = $name;
        $this->version  = $version;
        $this->terminal = new Terminal();

        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        parent::__construct($name, $version);

        $this->bootstrap();
    }

    /**
     * Add a command to the console.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return null|\Symfony\Component\Console\Command\Command|\Viserio\Component\Console\Command\Command
     */
    public function add(SymfonyCommand $command): ?SymfonyCommand
    {
        if ($command instanceof ViserioCommand) {
            if ($this->container !== null) {
                $command->setContainer($this->container);
            }

            $command->setInvoker($this->getInvoker());
        }

        return parent::add($command);
    }

    /**
     * Add a command to the console.
     *
     * @param string $expression defines the arguments and options of the command
     * @param array|callable|string $callable Called when the command is called.
     *                                          When using a container, this can be a "pseudo-callable"
     *                                          i.e. the name of the container entry to invoke.
     * @param array $aliases an array of aliases for the command
     *
     * @throws \Viserio\Component\Contract\Console\Exception\InvocationException
     *
     * @return \Viserio\Component\Console\Command\StringCommand
     */
    public function command(string $expression, $callable, array $aliases = []): StringCommand
    {
        $commandResolver = new CommandResolver($this->getInvoker(), $this);
        $command         = $commandResolver->resolve($expression, $callable, $aliases);

        $this->add($command);

        return $command;
    }

    /**
     * Run an console command by name.
     *
     * @param string $command
     * @param array $parameters
     * @param null|\Symfony\Component\Console\Output\OutputInterface $outputBuffer
     *
     * @throws \Throwable
     *
     * @return int
     */
    public function call(string $command, array $parameters = [], ?OutputInterface $outputBuffer = null): int
    {
        $this->lastOutput = $outputBuffer ?: new BufferedOutput();

        $this->setCatchExceptions(false);

        \array_unshift($parameters, $command);

        $result = $this->run(new ArrayInput($parameters), $this->lastOutput);

        $this->setCatchExceptions(true);

        return $result;
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output(): string
    {
        if (\method_exists($this->lastOutput, 'fetch')) {
            return $this->lastOutput->fetch();
        }

        return '';
    }

    /**
     * Define default values for the arguments of the command.
     *
     * @param string $commandName      name of the command
     * @param array  $argumentDefaults default argument values
     *
     * @return void
     */
    public function defaults(string $commandName, array $argumentDefaults = []): void
    {
        $command           = $this->get($commandName);
        $commandDefinition = $command->getDefinition();

        foreach ($argumentDefaults as $name => $default) {
            $argument = $commandDefinition->getArgument($name);
            $argument->setDefault($default);
        }
    }

    /**
     * Register an application starting bootstrapper.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public static function starting(Closure $callback): void
    {
        static::$bootstrappers[] = $callback;
    }

    /**
     * Clear the console application bootstrappers.
     *
     * @return void
     */
    public static function clearBootstrappers(): void
    {
        static::$bootstrappers = [];
    }

    /**
     * The PHP executable.
     *
     * @return string
     */
    public static function phpBinary(): string
    {
        $finder = (new PhpExecutableFinder())->find(false);

        return \escapeshellarg($finder === false ? '' : $finder);
    }

    /**
     * The Cerebro executable.
     *
     * @return string
     */
    public static function cerebroBinary(): string
    {
        $constant = \defined('CEREBRO_BINARY') ? \constant('CEREBRO_BINARY') : null;

        return  $constant !== null ? \escapeshellarg($constant) : 'cerebro';
    }

    /**
     * Format the given command as a fully-qualified executable command.
     *
     * @param string $string
     *
     * @return string
     */
    public static function formatCommandString(string $string): string
    {
        return \sprintf('%s %s %s', static::phpBinary(), static::cerebroBinary(), $string);
    }

    /**
     * Get the container instance.
     *
     * @throws \Viserio\Component\Contract\Console\Exception\LogicException
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if (! $this->container) {
            throw new LogicException('Container is not set up.');
        }

        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        \putenv('LINES=' . $this->terminal->getHeight());
        \putenv('COLUMNS=' . $this->terminal->getWidth());

        if ($input === null) {
            $input = new ArgvInput();
        }

        if ($output === null) {
            $output = new ConsoleOutput();
        }

        $renderException = function (Throwable $e) use ($output) {
            if (!$e instanceof Exception) {
                $e = new ErrorException($e->getMessage(), $e->getCode(), E_ERROR, $e->getFile(), $e->getLine());
            }

            if ($output instanceof ConsoleOutputInterface) {
                $this->renderException($e, $output->getErrorOutput());
            } else {
                $this->renderException($e, $output);
            }
        };

        $debugHandler = false;

        if ($phpHandler = set_exception_handler($renderException)) {
            restore_exception_handler();

            if (!is_array($phpHandler) || !$phpHandler[0] instanceof ErrorHandler) {
                $debugHandler = true;
            } elseif ($debugHandler = $phpHandler[0]->setExceptionHandler($renderException)) {
                $phpHandler[0]->setExceptionHandler($debugHandler);
            }
        }

        $this->configureIO($input, $output);

        try {
            $exitCode = $this->doRun($input, $output);
        } catch (Throwable $exception) {
            $exitCode = $exception->getCode();

            if ($this->eventManager !== null) {
                $this->eventManager->trigger($event = new ConsoleErrorEvent($this->runningCommand, $input, $output, $exception));

                $exitCode = $event->getExitCode();

                $this->eventManager->trigger(new ConsoleTerminateEvent($this->runningCommand, $input, $output, $exitCode));

                if ($exitCode === 0) {
                    return 0;
                }

                $exception = $event->getError();
            }

            if (! $this->areExceptionsCaught() || ! $exception instanceof Exception) {
                throw $exception;
            }

            $renderException($exception);

            if (\is_numeric($exitCode)) {
                $exitCode = (int) $exitCode;

                if ($exitCode === 0) {
                    $exitCode = 1;
                }
            } else {
                $exitCode = 1;
            }
        } finally {
            // if the exception handler changed, keep it
            // otherwise, unregister $renderException
            if (!$phpHandler) {
                if (set_exception_handler($renderException) === $renderException) {
                    restore_exception_handler();
                }
                restore_exception_handler();
            } elseif (!$debugHandler) {
                $finalHandler = $phpHandler[0]->setExceptionHandler(null);

                if ($finalHandler !== $renderException) {
                    $phpHandler[0]->setExceptionHandler($finalHandler);
                }
            }

            $this->runningCommand = null;
        }

        if ($this->isAutoExitEnabled()) {
            if ($exitCode > 255) {
                $exitCode = 255;
            }

            exit($exitCode);
        }

        return $exitCode;
    }

    /**
     * Runs the current command.
     *
     * If an event dispatcher has been attached to the application,
     * events are also dispatched during the life-cycle of the command.
     *
     * @param \Symfony\Component\Console\Command\Command        $command
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Throwable
     *
     * @return int 0 if everything went fine, or an error code
     */
    protected function doRunCommand(SymfonyCommand $command, InputInterface $input, OutputInterface $output): int
    {
        $this->runningCommand = $command;

        foreach ($command->getHelperSet() as $helper) {
            if ($helper instanceof InputAwareInterface) {
                $helper->setInput($input);
            }
        }

        if ($this->eventManager === null) {
            return $command->run($input, $output);
        }

        // bind before the console.command event, so the listeners have access to input options/arguments
        try {
            $command->mergeApplicationDefinition();
            $input->bind($command->getDefinition());
        } catch (ExceptionInterface $exception) {
            // ignore invalid options/arguments for now, to allow the event listeners to customize the InputDefinition
        }

        try {
            $this->eventManager->trigger($event = new ConsoleCommandEvent($command, $input, $output));

            if ($event->commandShouldRun()) {
                $exitCode = $command->run($input, $output);
            } else {
                $exitCode = ConsoleCommandEvent::RETURN_CODE_DISABLED;
            }
        } catch (Throwable $exception) {
            $this->eventManager->trigger($event = new ConsoleErrorEvent($command, $input, $output, $exception));

            if (($exitCode = $event->getExitCode()) !== 0) {
                throw $event->getError();
            }
        } finally {
            $this->eventManager->trigger($event = new ConsoleTerminateEvent($command, $input, $output, $exitCode));
        }

        return $event->getExitCode();
    }

    /**
     * Get the default input definitions for the applications.
     *
     * This is used to add the --env option to every available command.
     *
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption($this->getEnvironmentOption());

        return $definition;
    }

    /**
     * Get the global environment option for the definition.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return \Viserio\Component\Console\Input\InputOption
     */
    private function getEnvironmentOption(): InputOption
    {
        $message = 'The environment the command should run under.';

        return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
    }

    /**
     * Get configured invoker.
     *
     * @return \Viserio\Component\Support\Invoker
     */
    private function getInvoker(): Invoker
    {
        if (! $this->invoker) {
            $invoker = new Invoker();
            $invoker->injectByTypeHint(true)
                ->injectByParameterName(true);

            if ($this->container !== null) {
                $invoker->setContainer($this->container);
            }

            $this->invoker = $invoker;
        }

        return $this->invoker;
    }

    /**
     * Bootstrap the console application.
     *
     * @return void
     */
    private function bootstrap(): void
    {
        foreach (static::$bootstrappers as $bootstrapper) {
            $bootstrapper($this);
        }
    }
}
