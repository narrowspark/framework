<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

use Psr\Container\ContainerInterface;

class ValueDefinition extends AbstractSelfResolvingDefinition
{
    /**
     * The definition value.
     *
     * @var mixed
     */
    private $value;

    /**
     * Create a new ValueDefinition instance.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the definition value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ContainerInterface $container)
    {
        return $this->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(ContainerInterface $container) : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function replaceNestedDefinitions(callable $replacer): void
    {
        // value definitions cant be nested!
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $value = \var_export($this->value, true);

        if (\is_array($this->value)) {
            $value = '@todo use array print trait';
        }

        return \sprintf('Value (%s)', $value);
    }
}
