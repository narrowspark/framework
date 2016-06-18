<?php
namespace Viserio\Http;

use Psr\Http\Message\StreamInterface;
use Viserio\Contracts\Http\StreamFactory as StreamFactoryContract;

final class StreamFactory implements StreamFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function createStream(): StreamInterface
    {
        return Util::getStream();
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromCallback(callable $callback): StreamInterface
    {
        return Util::getStream($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($body): StreamInterface
    {
        return Util::getStream($body);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromString($body): StreamInterface
    {
        return Util::getStream($body);
    }
}
