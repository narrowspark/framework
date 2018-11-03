<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Events\Event as EventContract;
use Viserio\Component\Contract\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Events\Traits\EventTrait;

class KernelExceptionEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new kernel exception event.
     *
     * @param \Viserio\Component\Contract\Foundation\HttpKernel $kernel
     * @param \Psr\Http\Message\ServerRequestInterface          $serverRequest
     * @param \Psr\Http\Message\ResponseInterface               $response
     */
    public function __construct(
        HttpKernelContract $kernel,
        ServerRequestInterface $serverRequest,
        ResponseInterface $response
    ) {
        $this->name       = HttpKernelContract::EXCEPTION;
        $this->target     = $kernel;
        $this->parameters = ['server_request' => $serverRequest, 'response' => $response];
    }
}
