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
     * @param \Viserio\Contract\Events\EventManager $eventManager
     *
     * @return static
     */
    public function setEventManager(EventManagerContract $eventManager): self
    {
        $this->eventManager = $eventManager;

        return $this;
    }
}
