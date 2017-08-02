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
    protected $serverRequestFactory;

    /**
     * Set a ServerRequest instance.
     *
     * @param \Interop\Http\Factory\ServerRequestFactoryInterface $serverRequestFactory
     *
     * @return $this
     */
    public function setServerRequestFactory(ServerRequestFactoryInterface $serverRequestFactory)
    {
        $this->serverRequestFactory = $serverRequestFactory;

        return $this;
    }
}
