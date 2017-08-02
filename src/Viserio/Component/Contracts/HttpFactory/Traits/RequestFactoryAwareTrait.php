<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Traits;

use Interop\Http\Factory\RequestFactoryInterface;

trait RequestFactoryAwareTrait
{
    /**
     * A RequestFactory instance.
     *
     * @var \Interop\Http\Factory\RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * Set a RequestFactory instance.
     *
     * @param \Interop\Http\Factory\RequestFactoryInterface $requestFactory
     *
     * @return $this
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }
}
