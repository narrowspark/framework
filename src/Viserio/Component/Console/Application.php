<?php
declare(strict_types=1);
namespace Viserio\Component\Console;

use Closure;
use Interop\Container\ContainerInterface as ContainerContract;
use Invoker\Exception\InvocationException;
use RuntimeException;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessUtils;
use Throwable;
use Viserio\Component\Console\Command\Command as ViserioCommand;
use Viserio\Component\Console\Command\ExpressionParser as Parser;
use Viserio\Component\Console\Events\CerebroStartingEvent;
use Viserio\Component\Console\Events\CommandStartingEvent;
use Viserio\Component\Console\Events\CommandTerminatingEvent;
use Viserio\Component\Console\Events\ConsoleCommandEvent;
use Viserio\Component\Console\Events\ConsoleExceptionEvent;
use Viserio\Component\Console\Input\InputOption;
use Viserio\Component\Contracts\Console\Application as ApplicationContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Component\Support\Invoker;
use Symfony\Component\Console\Input\InputAwareInterface;

class Application extends SymfonyConsole implements ApplicationContract
{
    use ContainerAwareTrait;
    use EventsAwareTrait;

    /**
     * Console name.
     *
     * @var string
     */
    public $name = 'UNKNOWN';

    /**
     * Console version.
     *
     * @var string
     */
    public $version = 'UNKNOWN';

    /**
     * Command expression parser.
     *
     * @var \Viserio\Component\Console\Command\ExpressionParser
     */
    protected $expressionParser;

    /**
     * The output from the previous command.
     *
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    protected $lastOutput;

    /**
     * Invoker instance.
     *
     * @var \Viserio\Component\Support\Invoker
     */
    protected $invoker;

    /**
     * The console application bootstrappers.
     *
     * @var array
     */
    protected static $bootstrappers = [];

    /**
     * Create a new Cerebro console application.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $version
     * @param string                                $name
     */
    public function __construct(
        ContainerContract $container,
        string $version,
        string $name = 'Cerebro'
    ) {
        if (! defined('CEREBRO_BINARY')) {
            define('CEREBRO_BINARY', 'cerebro');
        }

        $this->name             = $name;
        $this->version          = $version;
        $this->container        = $container;
        $this->expressionParser = new Parser();

        $this->setAutoExit(false);
        $this->setCatchExceptions(false);
        $this->createCerebroEvent($container);

        parent::__construct($name, $version);

        $this->bootstrap();
    }

    /**
     * Add a command to the console.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return null|\Symfony\Component\Console\Command\Command
     */
    public function add(SymfonyCommand $command): ?SymfonyCommand
    {
        if ($command instanceof ViserioCommand) {
            $command->setContainer($this->getContainer());
            $command->setInvoker($this->getInvoker());
        }

        return parent::add($command);
    }

    /**
     * Add a command to the console.
     *
     * @param string                $expression defines the arguments and options of the command
     * @param callable|string|array $callable   Called when the command is called.
     *                                          When using a container, this can be a "pseudo-callable"
     *                                          i.e. the name of the container entry to invoke.
     * @param array                 $aliases    an array of aliases for the command
     *
     * @return \Symfony\Component\Console\Command\Command
     */
    public function command(string $expression, $callable, array $aliases = []): SymfonyCommand
    {
        $commandFunction = function (InputInterface $input, OutputInterface $output) use ($callable) {
            $parameters = array_merge(
                [
                    'input'  => $input,
                    'output' => $output,
                ],
                $input->getArguments(),
                $input->getOptions()
            );

            if ($callable instanceof Closure) {
                $callable = $callable->bindTo($this, $this);
            }

            try {
                $this->getInvoker()->call($callable, $parameters);
            } catch (InvocationException $exception) {
                throw new RuntimeException(sprintf(
                    "Impossible to call the '%s' command: %s",
                    $input->getFirstArgument(),
                    $exception->getMessage()
                ), 0, $exception);
            }
        };

        $command = $this->createCommand($expression, $commandFunction);
        $command->setAliases($aliases);

        $this->add($command);

        return $command;
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
     * Get console version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get console name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Register an application starting bootstrapper.
     *
     * @param \Closure $callback
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public static function starting(Closure $callback): void
    {
        static::$bootstrappers[] = $callback;
    }

    /**
     * Clear the console application bootstrappers.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public static function clearBootstrappers(): void
    {
        static::$bootstrappers = [];
    }

    /**
     * The PHP executable.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public static function phpBinary(): string
    {
        $finder = (new PhpExecutableFinder())->find(false);

        return ProcessUtils::escapeArgument($finder === false ? '' : $finder);
    }

    /**
     * The Cerebro executable.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public static function cerebroBinary(): string
    {
        return defined('CEREBRO_BINARY') ? ProcessUtils::escapeArgument(CEREBRO_BINARY) : 'cerebro';
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($this->events !== null) {
            $commandName = '';

            if ($input !== null) {
                $commandName = $this->getCommandName($input);
            }

            $this->getEventManager()->trigger(new CommandStartingEvent(
                $this,
                ['command_name' => $commandName, 'input' => $input, 'output' => $output]
            ));
        }

        return parent::run($input, $output);
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
     * @return int 0 if everything went fine, or an error code
     */
    protected function doRunCommand(SymfonyCommand $command, InputInterface $input, OutputInterface $output): int
    {
        foreach ($command->getHelperSet() as $helper) {
            if ($helper instanceof InputAwareInterface) {
                $helper->setInput($input);
            }
        }

        if ($this->events === null) {
            try {
                return $command->run($input, $output);
            } catch (Throwable $e) {
                throw new FatalThrowableError($e);
            }
        }

        // bind before the console.command event, so the listeners have access to input options/arguments
        try {
            $command->mergeApplicationDefinition();
            $input->bind($command->getDefinition());
        } catch (ExceptionInterface $e) {
            // ignore invalid options/arguments for now, to allow the event listeners to customize the InputDefinition
        }

        // don't bind the input again as it would override any input argument/option set from the command event in
        // addition to being useless
        $command->setInputBound(true);

        $this->getEventManager()->trigger($event = new ConsoleCommandEvent(
            $command,
            ['command_name' => $command->getName(), 'input' => $input, 'output' => $output]
        ));

        $exitCode = 0;

        if ($event->commandShouldRun()) {
            try {
                $e = $x = null;
                $exitCode = $command->run($input, $output);
            } catch (Throwable $x) {
                $e = new FatalThrowableError($x);
            }

            if (null !== $e) {
                $this->getEventManager()->trigger($event = new ConsoleExceptionEvent(
                    $command,
                    [
                        'command_name' => $command->getName(),
                        'input'        => $input,
                        'output'       => $output,
                        'exception'    => $e,
                        'exit_code'    => $e->getCode(),
                    ]
                ));

                if ($event->getException() !== $e) {
                    $x = $e = $event->getException();
                }

                $this->createTerminatingCommandEvent($command, $input, $output, $e->getCode());

                throw $x;
            }
        } else {
            $exitCode = ConsoleCommandEvent::RETURN_CODE_DISABLED;
        }

        $event = $this->createTerminatingCommandEvent($command, $input, $output, $exitCode);

        return $event->getExitCode();
    }

    /**
     * Create a command terminating event.
     *
     * @param \Symfony\Component\Console\Command\Command        $command
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int                                               $exitCode
     *
     * @return \Viserio\Component\Console\Events\CommandTerminatingEvent
     */
    protected function createTerminatingCommandEvent(
        $command,
        InputInterface $input,
        OutputInterface $output,
        int $exitCode
    ): CommandTerminatingEvent {
        $this->getEventManager()->trigger($event = new CommandTerminatingEvent(
            $command,
            ['command_name' => $command->getName(), 'input' => $input, 'output' => $output, 'exit_code' => $exitCode]
        ));

        return $event;
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
     * @return \Viserio\Component\Console\Input\InputOption
     */
    protected function getEnvironmentOption(): InputOption
    {
        $message = 'The environment the command should run under.';

        return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
    }

    /**
     * Create Command.
     *
     * @param string   $expression
     * @param callable $callable
     *
     * @return \Symfony\Component\Console\Command\Command
     */
    protected function createCommand(string $expression, callable $callable): SymfonyCommand
    {
        $result = $this->expressionParser->parse($expression);

        $command = new SymfonyCommand($result['name']);
        $command->getDefinition()->addArguments($result['arguments']);
        $command->getDefinition()->addOptions($result['options']);
        $command->setCode($callable);

        return $command;
    }

    /**
     * Get configured invoker.
     *
     * @return \Viserio\Component\Support\Invoker
     */
    protected function getInvoker(): Invoker
    {
        if (! $this->invoker) {
            $this->invoker = (new Invoker())
                ->injectByTypeHint(true)
                ->injectByParameterName(true)
                ->addResolver(new HyphenatedInputResolver())
                ->setContainer($this->getContainer());
        }

        return $this->invoker;
    }

    /**
     * Bootstrap the console application.
     */
    protected function bootstrap()
    {
        foreach (static::$bootstrappers as $bootstrapper) {
            $bootstrapper($this);
        }
    }

    /**
     * Creating a cerebro starting event.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private function createCerebroEvent(ContainerContract $container): void
    {
        if ($container->has(EventManagerContract::class)) {
            $this->events = $container->get(EventManagerContract::class);
            $this->events->trigger(new CerebroStartingEvent($this));
        }
    }
}
