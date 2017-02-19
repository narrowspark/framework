<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Events;

use Viserio\Component\Contracts\Console\Application as ApplicationContract;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Events\Traits\EventTrait;

class CerebroStartingEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new cerebro starting event.
     *
     * @param \Viserio\Component\Contracts\Console\Application $application
     *
     * @codeCoverageIgnore
     */
    public function __construct(ApplicationContract $application)
    {
        $this->name       = 'cerebro.starting';
        $this->target     = $application;
        $this->parameters = ['console' => $application];
    }
}
