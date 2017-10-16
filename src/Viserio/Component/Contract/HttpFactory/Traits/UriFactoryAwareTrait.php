<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Traits;

use Interop\Http\Factory\UriFactoryInterface;

trait UriFactoryAwareTrait
{
    /**
     * A UriFactory instance.
     *
     * @var \Interop\Http\Factory\UriFactoryInterface
     */
    protected $uriFactory;

    /**
     * Set a UriFactory instance.
     *
     * @param \Interop\Http\Factory\UriFactoryInterface $uriFactory
     *
     * @return $this
     */
    public function setUriFactory(UriFactoryInterface $uriFactory)
    {
        $this->uriFactory = $uriFactory;

        return $this;
    }
}
