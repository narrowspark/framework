<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http\Events;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Events\Traits\EventTrait;

class KernelRequestEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new kernel request event.
     *
     * @param \Viserio\Component\Contracts\Foundation\Kernel $kernel
     * @param \Psr\Http\Message\ServerRequestInterface       $serverRequest
     */
    public function __construct(KernelContract $kernel, ServerRequestInterface $serverRequest)
    {
        $this->name       = KernelContract::REQUEST;
        $this->target     = $kernel;
        $this->parameters = ['server_request' => $serverRequest];
    }
}
