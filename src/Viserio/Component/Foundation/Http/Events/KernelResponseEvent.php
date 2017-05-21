<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http\Events;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Events\Traits\EventTrait;

class KernelResponseEvent implements EventContract
{
    use EventTrait;

    /**
     * Modified or original response.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Create a new kernel response event.
     *
     * @param \Viserio\Component\Contracts\Foundation\Kernel $kernel
     * @param \Psr\Http\Message\ServerRequestInterface       $serverRequest
     * @param \Psr\Http\Message\ResponseInterface            $response
     */
    public function __construct(HttpKernelContract $kernel, ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $this->name       = HttpKernelContract::RESPONSE;
        $this->target     = $kernel;
        $this->parameters = ['server_request' => $serverRequest, 'response' => $response];
    }

    /**
     * Set a response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return void
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * Get a modified or original response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response ?? $this->parameters['response'];
    }
}
