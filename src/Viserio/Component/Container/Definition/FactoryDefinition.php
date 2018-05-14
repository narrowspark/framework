<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

class FactoryDefinition extends AbstractDefinition
{
    /**
     * Callable that returns the value.
     *
     * @var callable
     */
    private $factory;

    /**
     * Factory arguments.
     *
     * @var mixed[]
     */
    private $arguments = [];

    /**
     * Create a new FactoryDefinition instance.
     *
     * @param string $name Entry name
     * @param callable $factory Callable that returns the value associated to the entry name.
     * @param array $arguments Arguments to be passed to the callable
     */
    public function __construct(string $name, callable $factory, array $arguments = [])
    {
        $this->name = $name;
        $this->factory = $factory;
        $this->arguments = $arguments;
    }

    /**
     * Callable that returns the value associated to the entry name.
     *
     * @return callable
     */
    public function getCallable(): callable
    {
        return $this->factory;
    }

    /**
     * Returns the list of arguments to pass when calling the method.
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function replaceNestedDefinitions(callable $replacer): void
    {
        $this->arguments = \array_map($replacer, $this->arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return 'Factory';
    }
}