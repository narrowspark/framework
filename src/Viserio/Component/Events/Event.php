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
