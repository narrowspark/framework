<?php
declare(strict_types=1);
namespace Viserio\Events;

use InvalidArgumentException;
use Viserio\Contracts\Events\Event as EventContract;
use Viserio\Events\Traits\ValidateNameTrait;

class Event implements EventContract
{
    use ValidateNameTrait;

    /**
     * Event name.
     *
     * @var string
     */
    protected $name;

    /**
     * Event target/context an object OR static class name (string).
     *
     * @var object|string|null
     */
    protected $target;

    /**
     * Event parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Stop propagation.
     *
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * Create a new event instance.
     *
     * @param string             $eventName event name
     * @param string|object|null $target event context, object or classname
     * @param array              $parameters event parameters
     *
     * @throws InvalidArgumentException if event name is invalid
     */
    public function __construct(
        string $eventName,
        $target = null,
        $parameters = []
    ) {
        if (empty($eventName)) {
            throw new InvalidArgumentException('Event name cant be empty.');
        }

        $this->validateEventName($eventName);

        $this->name = $eventName;
        $this->target = $target;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritDoc}
     */
    public function getParams(): array
    {
        return $this->parameters;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * {@inheritDoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
