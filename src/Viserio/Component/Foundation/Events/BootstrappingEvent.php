<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Events;

use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Events\Traits\EventTrait;

class BootstrappingEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new bootstrapping event.
     *
     * @param string                                              $name
     * @param \Viserio\Component\Contracts\Foundation\Kernel $app
     */
    public function __construct(string $name, KernelContract $app)
    {
        $this->name   = 'bootstrapping.' . str_replace('\\', '', $name);
        $this->target = $app;
    }
}
