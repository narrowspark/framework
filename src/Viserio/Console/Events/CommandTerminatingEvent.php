<?php
declare(strict_types=1);
namespace Viserio\Console\Events;

use Viserio\Contracts\Console\Application as ApplicationContract;
use Viserio\Contracts\Events\Event as EventContract;
use Viserio\Events\Traits\EventTrait;

class CommandTerminatingEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new command terminating event.
     *
     * @param \Viserio\Contracts\Console\Application $application
     *
     * @codeCoverageIgnore
     */
    public function __construct(ApplicationContract $application, array $params)
    {
        $this->name       = 'command.terminating';
        $this->target     = $application;
        $this->parameters = $params;
    }
}
