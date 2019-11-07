<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Container\Definition\Traits;

use ReflectionParameter;
use Viserio\Contract\Container\Exception\OutOfBoundsException;

/**
 * @property array<string, bool> $changes
 *
 * @internal
 */
trait FactoryAwareTrait
{
    /**
     * List of parameter to pass when calling the class.
     *
     * @var mixed[]|ReflectionParameter[]
     */
    protected $classArguments = [];

    /**
     * The method name.
     *
     * @var string
     */
    protected $method;

    /**
     * Check if the method is static.
     *
     * @var bool
     */
    protected $static;

    /**
     * {@inheritdoc}
     */
    public function getClassArguments(): array
    {
        return $this->classArguments;
    }

    /**
     * {@inheritdoc}
     */
    public function setClassArguments(array $arguments)
    {
        foreach ($arguments as $key => $argument) {
            $this->setClassArgument($key, $argument);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Check if the method is static.
     *
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->static;
    }

    /**
     * Set true if the method is static or false if not.
     *
     * @param bool $static
     *
     * @return static
     */
    public function setStatic(bool $static)
    {
        $this->static = $static;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setClassArgument($key, $value)
    {
        $this->changes['class_arguments'] = true;

        $this->classArguments[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addClassArgument($argument)
    {
        $this->changes['class_arguments'] = true;

        $this->classArguments[] = $argument;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassArgument($index)
    {
        if (! \array_key_exists($index, $this->classArguments)) {
            throw new OutOfBoundsException(\sprintf('The class parameter [%s] doesn\'t exist.', $index));
        }

        return $this->classArguments[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function replaceClassArgument($index, $parameter)
    {
        if (\count($this->classArguments) === 0) {
            throw new OutOfBoundsException('Cannot replace parameter if none have been configured yet.');
        }

        if (\is_int($index) && ($index < 0 || $index > \count($this->classArguments) - 1)) {
            throw new OutOfBoundsException(\sprintf('The index [%d] is not in the range [0, %d].', $index, \count($this->classArguments) - 1));
        }

        if (! \array_key_exists($index, $this->classArguments)) {
            throw new OutOfBoundsException(\sprintf('The parameter [%s] doesn\'t exist.', $index));
        }

        return $this->setClassArgument($index, $parameter);
    }
}
