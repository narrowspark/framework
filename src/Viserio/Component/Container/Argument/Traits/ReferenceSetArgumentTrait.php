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

namespace Viserio\Component\Container\Argument\Traits;

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

trait ReferenceSetArgumentTrait
{
    /** @var \Viserio\Contract\Container\Definition\ReferenceDefinition[] */
    private $values;

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
     *
     * @return void
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
