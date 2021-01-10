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

namespace Viserio\Component\Container;

class Variable
{
    private string $name;

    /**
     * Create a new variable instance.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get variable name.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
