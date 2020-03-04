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

namespace Viserio\Component\Container\Argument\Traits;

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

trait ReferenceSetArgumentTrait
{
    /** @var \Viserio\Contract\Container\Definition\ReferenceDefinition[] */
    private array $values = [];

    /**
     * @param \Viserio\Contract\Container\Definition\ReferenceDefinition[] $values
     */
    public function __construct(array $values)
    {
        $this->setValue($values);
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
     */
    public function setValue(array $values): void
    {
        foreach ($values as $k => $v) {
            if ($v !== null && ! $v instanceof ReferenceDefinition) {
                throw new InvalidArgumentException(\sprintf('A [%s] must hold only Reference instances, [%s] given.', __CLASS__, \is_object($v) ? \get_class($v) : \gettype($v)));
            }
        }

        $this->values = $values;
    }
}
