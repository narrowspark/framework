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

interface ConditionArgument extends Argument
{
    /**
     * Returns a callback to manipulate definition.
     *
     * @return callable the given function should look like function ($definition) {...}
     */
    public function getCallback(): callable;
}
