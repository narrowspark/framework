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

namespace Viserio\Contract\Events\Traits;

use Viserio\Contract\Events\EventManager as EventManagerContract;

trait EventManagerAwareTrait
{
    /**
     * Event manager instance.
     *
     * @var null|\Viserio\Contract\Events\EventManager
     */
    protected $eventManager;

    /**
     * Set a event manager instance.
     *
     * @return static
     */
    public function setEventManager(EventManagerContract $eventManager): self
    {
        $this->eventManager = $eventManager;

        return $this;
    }
}
