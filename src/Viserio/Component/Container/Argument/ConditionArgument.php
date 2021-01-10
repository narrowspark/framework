<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Container\Argument;

use Viserio\Contract\Container\Argument\ConditionArgument as ConditionArgumentContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

final class ConditionArgument implements ConditionArgumentContract
{
    /** @var array<int, string|\Viserio\Contract\Container\Definition\ReferenceDefinition> */
    private array $values = [];

    /** @var callable */
    private $callback;

    /**
     * Create a new ConditionArgument instance.
     *
     * @param array<int, string|\Viserio\Contract\Container\Definition\ReferenceDefinition> $values
     */
    public function __construct(array $values, callable $callback)
    {
        $this->setValue($values);
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * The values in the set.
     *
     * @return array<int, string|\Viserio\Contract\Container\Definition\ReferenceDefinition>
     */
    public function getValue(): array
    {
        return $this->values;
    }

    /**
     * The service references to put in the set.
     *
     * @param mixed[] $values
     */
    public function setValue(array $values): void
    {
        foreach ($values as $v) {
            if (! \is_string($v) && ! $v instanceof ReferenceDefinitionContract) {
                throw new InvalidArgumentException(\sprintf('A [%s] must hold only strings and references, [%s] given.', __CLASS__, \is_object($v) ? \get_class($v) : \gettype($v)));
            }
        }

        $this->values = $values;
    }
}
