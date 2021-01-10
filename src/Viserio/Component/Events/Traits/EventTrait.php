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

trait EventTrait
{
    /**
     * Event name.
     *
     * @var string
     */
    protected $name;

    /**
     * Event target/context an object OR static class name (string).
     *
     * @var null|object|string
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
     * Get event name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get target/context from which event was triggered.
     *
     * @return null|object|string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Has this event indicated event propagation should stop?
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Get parameters passed to the event.
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
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
