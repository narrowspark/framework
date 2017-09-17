<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Traits;

use Interop\Http\Factory\StreamFactoryInterface;

trait StreamFactoryAwareTrait
{
    /**
     * A StreamFactory instance.
     *
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * Set a StreamFactory instance.
     *
     * @param \Interop\Http\Factory\StreamFactoryInterface $streamFactory
     *
     * @return $this
     */
    public function setStreamFactory(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }
}
