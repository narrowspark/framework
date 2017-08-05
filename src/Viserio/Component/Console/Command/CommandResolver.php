<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Command;

use Closure;
use InvalidArgumentException;
use Invoker\Exception\InvocationException as InvokerInvocationException;
use Invoker\Reflection\CallableReflection;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\Console\Exception\InvocationException;
use Viserio\Component\Contracts\Console\Exception\LogicException;
use Viserio\Component\Support\Invoker;
use Viserio\Component\Support\Str;

/**
 * Code in this class it taken from silly.
 *
 * See the original here: https://github.com/mnapoli/silly/blob/master/src/Application.php
 *
 * @author Matthieu Napoli https://github.com/mnapoli
 * @copyright Copyright (c) Matthieu Napoli
 */
final class CommandResolver
{
    /**
     * Invoker instance.
     *
     * @var \Viserio\Component\Support\Invoker
     */
    private $invoker;

    /**
     * Application instance.
     *
     * @var \Viserio\Component\Console\Application
     */
    private $console;

    /**
     * Create a new Cerebro console application.
     *
     * @param \Viserio\Component\Support\Invoker     $invoker
     * @param \Viserio\Component\Console\Application $console
     */
    public function __construct(Invoker $invoker, Application $console)
    {
        $this->invoker = $invoker;
        $this->console = $console;
    }

    /**
     * Resolve a command from expression.
     *
     * @param string                $expression Defines the arguments and options of the command
     * @param array|callable|string $callable   Called when the command is called.
     *                                          When using a container, this can be a "pseudo-callable"
     *                                          i.e. the name of the container entry to invoke.
     * @param array                 $aliases    an array of aliases for the command
     *
     * @throws \Viserio\Component\Contracts\Console\Exception\InvocationException
     *
     * @return \Viserio\Component\Console\Command\StringCommand
     */
    public function resolve(string $expression, $callable, array $aliases = []): StringCommand
    {
        $this->assertCallableIsValid($callable);

        $commandFunction = function (InputInterface $input, OutputInterface $output) use ($callable) {
            $parameters = \array_merge(
                [
                    // Injection by parameter name
                    'input'  => $input,
                    'output' => $output,
                    // Injections by type-hint
                    InputInterface::class  => $input,
                    OutputInterface::class => $output,
                    Input::class           => $input,
                    Output::class          => $output,
                    SymfonyStyle::class    => new SymfonyStyle($input, $output),
                ],
                $input->getArguments(),
                $input->getOptions()
            );

            if ($callable instanceof Closure) {
                $callable = $callable->bindTo($this->console, $this->console);
            }

            try {
                return $this->invoker->addResolver(new HyphenatedInputResolver())->call($callable, $parameters);
            } catch (InvokerInvocationException $exception) {
                throw new InvocationException(
                    \sprintf(
                        "Impossible to call the '%s' command: %s",
                        $input->getFirstArgument(),
                        $exception->getMessage()
                    ),
                    0,
                    $exception
                );
            }
        };

        $command = self::createCommand($expression, $commandFunction);
        $command->setAliases($aliases);
        $command->defaults($this->defaultsViaReflection($command, $callable));

        return $command;
    }

    /**
     * Create a new command.
     *
     * @param string   $expression
     * @param callable $callable
     *
     * @return \Viserio\Component\Console\Command\StringCommand
     */
    private static function createCommand(string $expression, callable $callable): StringCommand
    {
        $result = ExpressionParser::parse($expression);

        $command = new StringCommand($result['name']);
        $command->getDefinition()->addArguments($result['arguments']);
        $command->getDefinition()->addOptions($result['options']);
        $command->setCode($callable);

        return $command;
    }

    /**
     * Reflect default values from callable.
     *
     * @param \Viserio\Component\Console\Command\StringCommand $command
     * @param callable|string                                  $callable
     *
     * @return array
     */
    private function defaultsViaReflection(StringCommand $command, $callable): array
    {
        if (! \is_callable($callable)) {
            return [];
        }

        $function   = CallableReflection::create($callable);
        $definition = $command->getDefinition();
        $defaults   = [];

        foreach ($function->getParameters() as $parameter) {
            if (! $parameter->isDefaultValueAvailable()) {
                continue;
            }

            $parameterName      = $parameter->name;
            $hyphenatedCaseName = Str::snake($parameterName, '-');

            if ($definition->hasArgument($hyphenatedCaseName) || $definition->hasOption($hyphenatedCaseName)) {
                $parameterName = $hyphenatedCaseName;
            }

            if (! $definition->hasArgument($parameterName) && ! $definition->hasOption($parameterName)) {
                continue;
            }

            $defaults[$parameterName] = $parameter->getDefaultValue();
        }

        return $defaults;
    }

    /**
     * Check if callable is valid.
     *
     * @param mixed $callable
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    private function assertCallableIsValid($callable): void
    {
        try {
            $this->console->getContainer();
        } catch (LogicException $e) {
            if ($this->isStaticCallToNonStaticMethod($callable)) {
                [$class, $method] = $callable;

                $message  = "['{$class}', '{$method}'] is not a callable because '{$method}' is a static method.";
                $message .= " Either use [new {$class}(), '{$method}'] or configure a dependency injection container that supports autowiring.";

                throw new InvalidArgumentException($message);
            }
        }
    }

    /**
     * Check if the callable represents a static call to a non-static method.
     *
     * @param mixed $callable
     *
     * @return bool
     */
    private function isStaticCallToNonStaticMethod($callable): bool
    {
        if (\is_array($callable) && \is_string($callable[0])) {
            [$class, $method] = $callable;

            $reflection = new ReflectionMethod($class, $method);

            return ! $reflection->isStatic();
        }

        return false;
    }
}
