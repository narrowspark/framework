<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Traits;

use Psr\Http\Message\StreamFactoryInterface;

trait StreamFactoryAwareTrait
{
    /**
     * A StreamFactory instance.
     *
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * Set a StreamFactory instance.
     *
     * @param \Psr\Http\Message\StreamFactoryInterface $streamFactory
     *
     * @return $this
     */
    public function setStreamFactory(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }
}
