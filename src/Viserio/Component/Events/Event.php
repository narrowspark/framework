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

namespace Viserio\Component\Events;

use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Component\Events\Traits\ValidateNameTrait;
use Viserio\Contract\Events\Event as EventContract;
use Viserio\Contract\Events\Exception\InvalidArgumentException;

class Event implements EventContract
{
    use ValidateNameTrait;
    use EventTrait;

    /**
     * Create a new event instance.
     *
     * @param string             $eventName  event name
     * @param null|object|string $target     event context, object or classname
     * @param array              $parameters event parameters
     *
     * @throws \Viserio\Contract\Events\Exception\InvalidArgumentException if event name is invalid
     */
    public function __construct(string $eventName, $target = null, $parameters = [])
    {
        if (\trim($eventName) === '') {
            throw new InvalidArgumentException('Event name cant be empty.');
        }

        $this->validateEventName($eventName);

        $this->name = $eventName;
        $this->target = $target;
        $this->parameters = $parameters;
    }
}
