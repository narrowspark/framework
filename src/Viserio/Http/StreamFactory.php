<?php
namespace Viserio\Http;

use Psr\Http\Message\StreamInterface;
use Viserio\Contracts\Http\StreamFactory as StreamFactoryContract;

final class StreamFactory implements StreamFactoryContract
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createStream(): StreamInterface
    {
        return Util::getStream();
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createStreamFromCallback(callable $callback): StreamInterface
    {
        return Util::getStream($callback);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createStreamFromResource($body): StreamInterface
    {
        return Util::getStream($body);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createStreamFromString(string $body): StreamInterface
    {
        return Util::getStream($body);
    }
}
