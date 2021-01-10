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

namespace Viserio\Component\Events\Traits;

use Viserio\Contract\Events\Exception\InvalidArgumentException;

trait ValidateNameTrait
{
    /**
     * The event name must only contain the characters A-Z, a-z, 0-9, _, and '.'.
     *
     * @throws \Viserio\Contract\Events\Exception\InvalidArgumentException
     */
    protected function validateEventName(string $eventName): void
    {
        \preg_match_all('/([a-zA-Z0-9_\\.]+)/', $eventName, $matches);

        if (\count($matches[0]) >= 2) {
            throw new InvalidArgumentException('The event name must only contain the characters A-Z, a-z, 0-9, _, and \'.\'.');
        }
    }
}
