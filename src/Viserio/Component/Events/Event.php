<?php
declare(strict_types=1);
namespace Viserio\Component\Events;

use Viserio\Component\Contract\Events\Event as EventContract;
use Viserio\Component\Contract\Events\Exception\InvalidArgumentException;
use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Component\Events\Traits\ValidateNameTrait;

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
     * @throws \Viserio\Component\Contract\Events\Exception\InvalidArgumentException if event name is invalid
     */
    public function __construct(
        string $eventName,
        $target = null,
        $parameters = []
    ) {
        if (\trim($eventName) === '') {
            throw new InvalidArgumentException('Event name cant be empty.');
        }

        $this->validateEventName($eventName);

        $this->name       = $eventName;
        $this->target     = $target;
        $this->parameters = $parameters;
    }
}
