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

namespace Viserio\Component\Events\Traits;

use Viserio\Contract\Events\Exception\InvalidArgumentException;

trait ValidateNameTrait
{
    /**
     * The event name must only contain the characters A-Z, a-z, 0-9, _, and '.'.
     *
     * @param string $eventName
     *
     * @throws \Viserio\Contract\Events\Exception\InvalidArgumentException
     *
     * @return void
     */
    protected function validateEventName(string $eventName): void
    {
        \preg_match_all('/([a-zA-Z0-9_\\.]+)/', $eventName, $matches);

        if (\count($matches[0]) >= 2) {
            throw new InvalidArgumentException('The event name must only contain the characters A-Z, a-z, 0-9, _, and \'.\'.');
        }
    }
}
