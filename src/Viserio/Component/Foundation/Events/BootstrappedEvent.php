<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Events;

use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;

class BootstrappedEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new bootstrapped event.
     *
     * @param string                                              $name
     * @param \Viserio\Component\Contracts\Foundation\Application $app
     */
    public function __construct(string $name, ApplicationContract $app)
    {
        $this->name       = 'bootstrapped.' . str_replace('\\', '', $name);
        $this->target     = $app;
    }
}
