<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Traits;

use Psr\Http\Message\RequestFactoryInterface;

trait RequestFactoryAwareTrait
{
    /**
     * A RequestFactory instance.
     *
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * Set a RequestFactory instance.
     *
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory
     *
     * @return $this
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }
}
