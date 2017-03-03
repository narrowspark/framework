<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Events;

use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Events\Traits\EventTrait;

class RouteMatchedEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new route matched event.
     *
     * @param       $router
     * @param array $param
     *
     * @codeCoverageIgnore
     */
    public function __construct($router, array $param)
    {
        $this->name       = 'route.matched';
        $this->target     = $router;
        $this->parameters = $param;
    }
}
