<?php
namespace Viserio\Console;

use Interop\Container\ContainerInterface as ContainerContract;
use Invoker\Exception\InvocationException;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use RuntimeException;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Viserio\Console\Command\Command as ViserioCommand;
use Viserio\Console\Command\ExpressionParser as Parser;
use Viserio\Console\Input\InputArgument;
use Viserio\Console\Input\InputOption;
use Viserio\Contracts\Console\Application as ApplicationContract;

class Application extends SymfonyConsole implements ApplicationContract
{
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
     * The Container instance.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * The event dispatcher implementation.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $events;

    /**
     * Command expression parser.
     *
     * @var \Viserio\Console\Command\ExpressionParser
     */
    protected $expressionParser;

    /**
     * Invoker instance.
     *
     * @var InvokerInterface
     */
    protected $invoker;

    /**
     * The output from the previous command.
     *
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    protected $lastOutput;

    /**
     * Create a new Cerebro console application.
     *
     * @param ContainerContract                                           $container
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $events
     * @param string                                                      $version
     * @param string                                                      $name
     */
    public function __construct(
        ContainerContract $container,
        EventDispatcherInterface $events,
        $version,
        $name = 'Narrowspark Framework'
    ) {
        $this->name      = $name;
        $this->version   = $version;
        $this->container = $container;
        $this->events    = $events;

        $this->expressionParser = new Parser();
        $this->initInvoker();

        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        parent::__construct($this->getName(), $this->getVersion());
    }

    /**
     * Add a command to the console.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return SymfonyCommand|null
     */
    public function add(SymfonyCommand $command)
    {
        if ($command instanceof ViserioCommand) {
            $command->setContainer($this->getContainer());
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
     *
     * @return SymfonyCommand|null
     */
    public function command($expression, $callable)
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

            try {
                $this->invoker->call($callable, $parameters);
            } catch (InvocationException $e) {
                throw new RuntimeException(sprintf(
                    "Impossible to call the '%s' command: %s",
                    $input->getFirstArgument(),
                    $e->getMessage()
                ), 0, $e);
            }
        };

        $command = $this->createCommand($expression, $commandFunction);

        $this->add($command);

        return $command;
    }

    /**
     * Define default values for the arguments of the command.
     *
     * @param string $commandName      Name of the command.
     * @param array  $argumentDefaults Default argument values.
     */
    public function defaults($commandName, array $argumentDefaults = [])
    {
        $command = $this->get($commandName);
        $commandDefinition = $command->getDefinition();

        foreach ($argumentDefaults as $name => $default) {
            $argument = $commandDefinition->getArgument($name);
            $argument->setDefault($default);
        }
    }

    /**
     * Get the container instance.
     *
     * @return ContainerContract
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns a service contained in the application container or null if none is found with that name.
     *
     * This is a convenience method used to retrieve an element from the Application container without having to assign
     * the results of the getContainer() method in every call.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getService($name)
    {
        return $this->getContainer()->has($name) ? $this->getContainer()->get($name) : null;
    }

    /**
     * Get console version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get console name.
     *
     * @return string
     */
    public function getName()
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
    protected function getDefaultInputDefinition()
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
    protected function getEnvironmentOption()
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
     * @return SymfonyCommand
     */
    protected function createCommand($expression, callable $callable)
    {
        $result = $this->expressionParser->parse($expression);

        $command = new SymfonyCommand($result['name']);
        $command->getDefinition()->addArguments($result['arguments']);
        $command->getDefinition()->addOptions($result['options']);
        $command->setCode($callable);

        return $command;
    }

    /**
     * @return \Invoker\InvokerInterface
     */
    private function initInvoker()
    {
        if (!$this->invoker) {
            $chain = [
                new TypeHintContainerResolver($this->getContainer()),
                new ParameterNameContainerResolver($this->getContainer()),
                new NumericArrayResolver,
                new AssociativeArrayResolver,
                new DefaultValueResolver,
            ];
            $parameterResolver = new ResolverChain($chain);
            $this->invoker = new Invoker($parameterResolver, $this->getContainer());
        }

        return $this->invoker;
    }
}
