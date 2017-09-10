<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Events;

interface Event
{
    /**
     * Get event name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get target/context from which event was triggered.
     *
     * @return null|object|string
     */
    public function getTarget();

    /**
     * Get parameters passed to the event.
     *
     * @return array
     */
    public function getParams(): array;

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function isPropagationStopped(): bool;
}
