<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Traits;

use Interop\Http\Factory\ServerRequestInterface;
use RuntimeException;

trait ServerRequestFactoryAwareTrait
{
    /**
     * A ServerRequest instance.
     *
     * @var \Interop\Http\Factory\ServerRequestInterface
     */
    protected $serverRequest;

    /**
     * Set a ServerRequest instance.
     *
     * @param \Interop\Http\Factory\ServerRequestInterface $serverRequest
     *
     * @return $this
     */
    public function setServerRequest(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;

        return $this;
    }

    /**
     * Get the ServerRequest instance.
     *
     * @throws \RuntimeException
     *
     * @return \Interop\Http\Factory\ServerRequestInterface
     */
    public function getServerRequest(): ServerRequestInterface
    {
        if (!$this->serverRequest) {
            throw new RuntimeException('Instance implementing \Interop\Http\Factory\ServerRequestInterface is not set up.');
        }

        return $this->serverRequest;
    }
}
