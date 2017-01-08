<?php
declare(strict_types=1);
namespace Viserio\Console\Events;

use Viserio\Contracts\Console\Application as ApplicationContract;
use Viserio\Contracts\Events\Event as EventContract;
use Viserio\Events\Traits\EventTrait;

class CerebroStartingEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new cerebro starting event.
     *
     * @param \Viserio\Contracts\Console\Application $application
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
