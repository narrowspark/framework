<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Traits;

use Interop\Http\Factory\UriFactoryInterface;
use RuntimeException;

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

    /**
     * Get the UriFactory instance.
     *
     * @throws \RuntimeException
     *
     * @return \Interop\Http\Factory\UriFactoryInterface
     */
    public function getUriFactory(): UriFactoryInterface
    {
        if (! $this->uriFactory) {
            throw new RuntimeException('Instance implementing [\Interop\Http\Factory\UriFactoryInterface] is not set up.');
        }

        return $this->uriFactory;
    }
}
