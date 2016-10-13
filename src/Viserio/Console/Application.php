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
use Viserio\Console\Command\Command as ViserioCommand;
use Viserio\Console\Command\ExpressionParser as Parser;
use Viserio\Console\Input\InputOption;
use Viserio\Contracts\Console\Application as ApplicationContract;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Support\Invoker;

class Application extends SymfonyConsole implements ApplicationContract
{
    use EventsAwareTrait;
    use ContainerAwareTrait;

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
     * Create a new Cerebro console application.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Viserio\Contracts\Events\Dispatcher  $events
     * @param string                                $version
     * @param string                                $name
     */
    public function __construct(
        ContainerContract $container,
        DispatcherContract $events,
        string $version,
        string $name = 'Cerebro'
    ) {
        $this->name = $name;
        $this->version = $version;

        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        parent::__construct($name, $version);

        $this->container = $container;
        $this->events = $events;
        $this->expressionParser = new Parser();

        $this->events->trigger('console.starting', [$this]);
    }

    /**
     * Add a command to the console.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return null|\Symfony\Component\Console\Command\Command
     */
    public function add(SymfonyCommand $command)
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
     * @param string                $expression Defines the arguments and options of the command.
     * @param callable|string|array $callable   Called when the command is called.
     *                                          When using a container, this can be a "pseudo-callable"
     *                                          i.e. the name of the container entry to invoke.
     * @param array                 $aliases    An array of aliases for the command.
     *
     * @return \Symfony\Component\Console\Command\Command
     */
    public function command(string $expression, $callable, array $aliases = []): SymfonyCommand
    {
        $commandFunction = function (InputInterface $input, OutputInterface $output) use ($callable) {
            $parameters = array_merge(
                [
                    'input' => $input,
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
     * @param string $commandName      Name of the command.
     * @param array  $argumentDefaults Default argument values.
     */
    public function defaults(string $commandName, array $argumentDefaults = [])
    {
        $command = $this->get($commandName);
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
}
