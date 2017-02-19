<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Traits;

use Interop\Http\Factory\StreamFactoryInterface;
use RuntimeException;

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

    /**
     * Get the StreamFactory instance.
     *
     * @throws \RuntimeException
     *
     * @return \Interop\Http\Factory\StreamFactoryInterface
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
        if (! $this->streamFactory) {
            throw new RuntimeException('Instance implementing \Interop\Http\Factory\StreamFactoryInterface is not set up.');
        }

        return $this->streamFactory;
    }
}
