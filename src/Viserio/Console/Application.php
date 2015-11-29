<?php
namespace Viserio\Console;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

use Interop\Container\ContainerInterface as ContainerContract;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Viserio\Console\Command\Command as ViserioCommand;
use Viserio\Console\Command\ExpressionParser as Parser;
use Viserio\Console\Input\InputArgument;
use Viserio\Console\Input\InputOption;

/**
 * Application.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class Application extends SymfonyConsole
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
     * @var \Invoker\InvokerInterface|null
     */
    protected $invoker;

    /**
     * Create a new Cerebro console application.
     *
     * @param ContainerContract                                           $container
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $events
     */
    public function __construct(ContainerContract $container, EventDispatcherInterface $events)
    {
        $this->expressionParser = new Parser();

        $this->container = $container;
        $this->events = $events;
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
     * Add a command, resolving through the application.
     *
     * @param string $command
     *
     * @return \Symfony\Component\Console\Command\Command|null
     */
    public function resolve($command)
    {
        return $this->add($this->getContainer()->make($command));
    }

    /**
     * Resolve an array of commands through the application.
     *
     * @param array|mixed $commands
     *
     * @return $this
     */
    public function resolveCommands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        foreach ($commands as $command) {
            $this->resolve($command);
        }

        return $this;
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

            $this->getInvoker()->call($callable, $parameters);
        };

        $command = $this->createCommand($expression, $commandFunction);

        $this->add($command);
    }

    /**
     * Define descriptions for the command and it's arguments/options.
     *
     * @param string $commandName                   Name of the command.
     * @param string $description                   Description of the command.
     * @param array  $argumentAndOptionDescriptions Descriptions of the arguments and options.
     *
     * @api
     */
    public function descriptions($commandName, $description, array $argumentAndOptionDescriptions = [])
    {
        $command = $this->get($commandName);
        $commandDefinition = $command->getDefinition();

        $command->setDescription($description);

        foreach ($argumentAndOptionDescriptions as $name => $value) {
            if (strpos($name, '--') === 0) {
                $name = substr($name, 2);
                $this->setOptionDescription($commandDefinition, $name, $value);
            } else {
                $this->setArgumentDescription($commandDefinition, $name, $value);
            }
        }
    }

    /**
     * Define default values for the arguments of the command.
     *
     * @param string $commandName      Name of the command.
     * @param array  $argumentDefaults Default argument values.
     *
     * @api
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
     * Get the Narrowspark application instance.
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
     * @param string $name Name of the service.
     *
     * @see self::getContainer()
     *
     * @api
     *
     * @return mixed|null
     */
    public function getService($name)
    {
        return isset($this->container[$name]) ? $this->container[$name] : null;
    }

    /**
     * Set console version.
     *
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
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
     * Set console name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * [setArgumentDescription description].
     *
     * @param InputDefinition $definition
     * @param string          $name
     * @param string          $description
     */
    protected function setArgumentDescription(InputDefinition $definition, $name, $description)
    {
        $argument = $definition->getArgument($name);

        if ($argument instanceof InputArgument) {
            $argument->setDescription($description);
        }
    }

    /**
     * [setOptionDescription description].
     *
     * @param InputDefinition $definition
     * @param string          $name
     * @param string          $description
     */
    protected function setOptionDescription(InputDefinition $definition, $name, $description)
    {
        $argument = $definition->getOption($name);

        if ($argument instanceof InputOption) {
            $argument->setDescription($description);
        }
    }

    /**
     * @return \Invoker\InvokerInterface
     */
    private function getInvoker()
    {
        if (!$this->invoker) {
            $chain = [
                new NumericArrayResolver,
                new AssociativeArrayResolver,
                new DefaultValueResolver
            ];

            $parameterResolver = new ResolverChain($chain);

            $this->invoker = new Invoker($parameterResolver, $this);
        }

        return $this->invoker;
    }
}
