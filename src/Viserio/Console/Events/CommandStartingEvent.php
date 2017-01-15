<?php
declare(strict_types=1);
namespace Viserio\Console\Events;

use Viserio\Contracts\Console\Application as ApplicationContract;
use Viserio\Contracts\Events\Event as EventContract;
use Viserio\Events\Traits\EventTrait;

class CommandStartingEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new command starting event.
     *
     * @param \Viserio\Contracts\Console\Application $application
     * @param array                                  $params
     *
     * @codeCoverageIgnore
     */
    public function __construct(ApplicationContract $application, array $params)
    {
        $this->name       = 'command.starting';
        $this->target     = $application;
        $this->parameters = $params;
    }
}
