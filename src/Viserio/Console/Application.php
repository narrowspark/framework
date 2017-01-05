<?php
declare(strict_types=1);
namespace Viserio\Console;

use Closure;
use Interop\Container\ContainerInterface as ContainerContract;
use Invoker\Exception\InvocationException;
use RuntimeException;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessUtils;
use Viserio\Console\Command\Command as ViserioCommand;
use Viserio\Console\Command\ExpressionParser as Parser;
use Viserio\Console\Input\InputOption;
use Viserio\Contracts\Console\Application as ApplicationContract;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Support\Invoker;

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
     * @var \Viserio\Console\Command\ExpressionParser
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
     * @var \Viserio\Support\Invoker
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

        $this->name    = $name;
        $this->version = $version;

        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        parent::__construct($name, $version);

        $this->container        = $container;
        $this->expressionParser = new Parser();

        if ($this->events !== null) {
            $this->events->trigger('cerebro.starting', [$this]);
        }

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
     *
     * @codeCoverageIgnore
     */
    public static function phpBinary(): string
    {
        $finder = (new PhpExecutableFinder())->find(false);

        return ProcessUtils::escapeArgument($finder ?? '');
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
            if ($input instanceof InputInterface) {
                $commandName = $this->getCommandName($input);
            }

            $this->events->trigger('command.starting', [$commandName, $input]);
        }

        $exitCode = parent::run($input, $output);

        if ($this->events !== null) {
            $this->events->trigger('command.terminating', [$commandName, $input, $exitCode]);
        }

        return $exitCode;
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
     * @return \Viserio\Console\Input\InputOption
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
     * @return \Viserio\Support\Invoker
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
}
