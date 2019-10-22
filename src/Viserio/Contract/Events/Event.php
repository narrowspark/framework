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

namespace Viserio\Contract\Events;

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
