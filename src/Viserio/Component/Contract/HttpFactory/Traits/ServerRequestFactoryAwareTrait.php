<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Traits;

use Interop\Http\Factory\ServerRequestFactoryInterface;

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
