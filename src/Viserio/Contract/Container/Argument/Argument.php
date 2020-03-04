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

namespace Viserio\Contract\Container\Argument;

interface Argument
{
    /**
     * The values in the set.
     *
     * @return \Viserio\Contract\Container\Definition\ReferenceDefinition[]
     */
    public function getValue(): array;

    /**
     * The service references to put in the set.
     *
     * @param \Viserio\Contract\Container\Definition\ReferenceDefinition[] $values
     */
    public function setValue(array $values): void;
}
