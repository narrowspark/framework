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

use Viserio\Contract\Container\Exception\OutOfBoundsException;

/**
 * @property array<string, bool> $changes
 *
 * @internal
 */
trait ArgumentAwareTrait
{
    /**
     * List of parameter to pass when calling the class.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * {@inheritdoc}
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function setArguments(array $arguments)
    {
        foreach ($arguments as $key => $argument) {
            $this->setArgument($key, $argument);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addArgument($argument)
    {
        $this->changes['arguments'] = true;

        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setArgument($key, $argument)
    {
        $this->changes['arguments'] = true;

        $this->arguments[$key] = $argument;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument($index)
    {
        if (! isset($this->arguments[$index])) {
            throw new OutOfBoundsException(\sprintf('The parameter [%s] doesn\'t exist.', $index));
        }

        return $this->arguments[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function replaceArgument($index, $parameter)
    {
        if (\count($this->arguments) === 0) {
            throw new OutOfBoundsException('Cannot replace parameter if none have been configured yet.');
        }

        if (\is_int($index) && ($index < 0 || $index > \count($this->arguments) - 1)) {
            throw new OutOfBoundsException(\sprintf('The index [%d] is not in the range [0, %d].', $index, \count($this->arguments) - 1));
        }

        if (! isset($this->arguments[$index])) {
            throw new OutOfBoundsException(\sprintf('The parameter [%s] doesn\'t exist.', $index));
        }

        return $this->setArgument($index, $parameter);
    }
}
