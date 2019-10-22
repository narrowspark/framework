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

namespace Viserio\Component\Container\Argument;

use Viserio\Contract\Container\Argument\ConditionArgument as ConditionArgumentContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

final class ConditionArgument implements ConditionArgumentContract
{
    /** @var mixed[] */
    private $values;

    /** @var callable */
    private $callback;

    /**
     * Create a new ConditionArgument instance.
     *
     * @param mixed[]  $values
     * @param callable $callback
     */
    public function __construct(array $values, callable $callback)
    {
        $this->setValue($values);
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * The values in the set.
     *
     * @return \Viserio\Contract\Container\Definition\ReferenceDefinition[]
     */
    public function getValue(): array
    {
        return $this->values;
    }

    /**
     * The service references to put in the set.
     *
     * @param \Viserio\Contract\Container\Definition\ReferenceDefinition[] $values
     *
     * @return void
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
