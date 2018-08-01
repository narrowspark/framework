<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Traits;

use Psr\Http\Message\UriFactoryInterface;

trait UriFactoryAwareTrait
{
    /**
     * A UriFactory instance.
     *
     * @var \Psr\Http\Message\UriFactoryInterface
     */
    protected $uriFactory;

    /**
     * Set a UriFactory instance.
     *
     * @param \Psr\Http\Message\UriFactoryInterface $uriFactory
     *
     * @return $this
     */
    public function setUriFactory(UriFactoryInterface $uriFactory)
    {
        $this->uriFactory = $uriFactory;

        return $this;
    }
}
