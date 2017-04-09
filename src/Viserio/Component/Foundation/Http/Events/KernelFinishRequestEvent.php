<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http\Events;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Events\Traits\EventTrait;

class KernelFinishRequestEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new kernel request finish event.
     *
     * @param \Viserio\Component\Contracts\Foundation\Kernel $kernel
     * @param \Psr\Http\Message\ServerRequestInterface       $serverRequest
     * @param \Psr\Http\Message\ResponseInterface            $response
     */
    public function __construct(KernelContract $kernel, ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $this->name       = KernelContract::FINISH_REQUEST;
        $this->target     = $kernel;
        $this->parameters = ['server_request' => $serverRequest, 'response' => $response];
    }
}
