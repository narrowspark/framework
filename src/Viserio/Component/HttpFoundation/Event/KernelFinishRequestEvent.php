<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Event;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Events\Event as EventContract;
use Viserio\Component\Contract\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Events\Traits\EventTrait;

class KernelFinishRequestEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new kernel request finish event.
     *
     * @param \Viserio\Component\Contract\Foundation\HttpKernel $kernel
     * @param \Psr\Http\Message\ServerRequestInterface          $serverRequest
     */
    public function __construct(HttpKernelContract $kernel, ServerRequestInterface $serverRequest)
    {
        $this->name       = HttpKernelContract::FINISH_REQUEST;
        $this->target     = $kernel;
        $this->parameters = ['server_request' => $serverRequest];
    }
}
