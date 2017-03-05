<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Traits;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use RuntimeException;

trait ServerRequestFactoryAwareTrait
{
    /**
     * A ServerRequest instance.
     *
     * @var \Interop\Http\Factory\ServerRequestFactoryInterface
     */
    protected $serverRequest;

    /**
     * Set a ServerRequest instance.
     *
     * @param \Interop\Http\Factory\ServerRequestFactoryInterface $serverRequest
     *
     * @return $this
     */
    public function setServerRequestFactory(ServerRequestFactoryInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;

        return $this;
    }

    /**
     * Get the ServerRequest instance.
     *
     * @throws \RuntimeException
     *
     * @return \Interop\Http\Factory\ServerRequestFactoryInterface
     */
    public function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        if (! $this->serverRequest) {
            throw new RuntimeException('Instance implementing [\Interop\Http\Factory\ServerRequestFactoryInterface] is not set up.');
        }

        return $this->serverRequest;
    }
}
