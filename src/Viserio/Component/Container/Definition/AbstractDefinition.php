<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

abstract class AbstractDefinition
{
    /**
     * Entry name.
     *
     * @var null|string
     */
    protected $name;

    /**
     * Returns the name of the entry that was requested by the container.
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Apply a callable that replaces the definitions nested in this definition.
     *
     * @param callable $replacer
     *
     * @return void
     */
    abstract public function replaceNestedDefinitions(callable $replacer): void;

    /**
     * Definitions can be cast to string for debugging information.
     *
     * @return string
     */
    abstract public function __toString(): string;
}