<?php
declare(strict_types=1);
namespace Viserio\Http;

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;
use Viserio\Contracts\Http\StreamFactory as StreamFactoryContract;
use Viserio\Http\Stream\PumpStream;

final class StreamFactory implements StreamFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function createStream(): StreamInterface
    {
        return new Stream(fopen('php://temp', 'r+'));
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromCallback(callable $callback): StreamInterface
    {
        return new PumpStream($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($body): StreamInterface
    {
        if (gettype($body) === 'resource') {
            return new Stream($body);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . gettype($body));
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromString(string $body): StreamInterface
    {
        $stream = fopen('php://temp', 'r+');

        if ($body !== '') {
            fwrite($stream, $body);
            fseek($stream, 0);
        }

        return new Stream($stream);
    }
}
