<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Foundation\Terminable as TerminableContract;
use Viserio\Component\Events\Traits\EventTrait;

class KernelTerminateEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new kernel terminate event.
     *
     * @param \Viserio\Component\Contracts\Foundation\Terminable $kernel
     * @param \Psr\Http\Message\ServerRequestInterface           $serverRequest
     * @param \Psr\Http\Message\ResponseInterface                $response
     */
    public function __construct(TerminableContract $kernel, ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $this->name       = TerminableContract::TERMINATE;
        $this->target     = $kernel;
        $this->parameters = ['server_request' => $serverRequest, 'response' => $response];
    }
}
