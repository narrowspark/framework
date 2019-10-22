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

use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

final class ClosureArgument implements ArgumentContract
{
    /** @var \Viserio\Contract\Container\Definition\ReferenceDefinition[] */
    private $values;

    /**
     * Create a new ClosureArgument instance.
     *
     * @param \Viserio\Contract\Container\Definition\ReferenceDefinition $reference
     */
    public function __construct(ReferenceDefinitionContract $reference)
    {
        $this->values = [$reference];
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): array
    {
        return $this->values;
    }

    /**
     * The service references to put in the set.
     *
     * @param mixed[]|\Viserio\Contract\Container\Definition\ReferenceDefinition[] $values
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function setValue(array $values): void
    {
        if (\array_keys($values) !== [0] || ! $values[0] instanceof ReferenceDefinitionContract) {
            throw new InvalidArgumentException('A [Viserio\Component\Container\Argument\ClosureArgument] must hold one and only one reference.');
        }

        $this->values = $values;
    }
}
