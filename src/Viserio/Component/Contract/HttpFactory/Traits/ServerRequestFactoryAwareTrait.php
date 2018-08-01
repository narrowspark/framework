<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Traits;

use Psr\Http\Message\ServerRequestFactoryInterface;

trait ServerRequestFactoryAwareTrait
{
    /**
     * A ServerRequest instance.
     *
     * @var \Psr\Http\Message\ServerRequestFactoryInterface
     */
    protected $serverRequestFactory;

    /**
     * Set a ServerRequest instance.
     *
     * @param \Psr\Http\Message\ServerRequestFactoryInterface $serverRequestFactory
     *
     * @return $this
     */
    public function setServerRequestFactory(ServerRequestFactoryInterface $serverRequestFactory)
    {
        $this->serverRequestFactory = $serverRequestFactory;

        return $this;
    }
}
