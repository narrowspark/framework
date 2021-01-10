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

namespace Viserio\Contract\Events;

interface Event
{
    /**
     * Get event name.
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
     */
    public function getParams(): array;

    /**
     * Has this event indicated event propagation should stop?
     */
    public function isPropagationStopped(): bool;
}
