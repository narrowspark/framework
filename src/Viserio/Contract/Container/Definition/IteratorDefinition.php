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

namespace Viserio\Contract\Container\Definition;

interface IteratorDefinition extends Definition
{
    /**
     * Returns the argument.
     */
    public function getArgument(): ?array;

    /**
     * Set a array argument.
     *
     * @param array<int|string, mixed> $argument
     *
     * @return static
     */
    public function setArgument(array $argument);
}
