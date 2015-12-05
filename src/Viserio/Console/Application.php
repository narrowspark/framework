<?php
namespace Viserio\Console;

use Interop\Container\ContainerInterface as ContainerContract;
use Invoker\Exception\InvocationException;
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
use Viserio\Support\Invoker;
use Viserio\Support\Traits\ContainerAwareTrait;

class Application extends SymfonyConsole implements ApplicationContract
{
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
        $this->events    = $events;

        $this->setContainer($container);
        $this->expressionParser = new Parser();
        $this->setInvoker();

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
                $this->getInvoker()->call($callable, $parameters);
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
     * Set configured invoker.
     */
    protected function setInvoker()
    {
        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($this->getContainer());
    }

    /**
     * Get configured invoker.
     *
     * @return \Viserio\Support\Invoker
     */
    protected function getInvoker()
    {
        return $this->invoker;
    }
}
