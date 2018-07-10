<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\PumpStream;
use Viserio\Component\Http\Util;

final class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = \fopen('php://memory', 'rb+');

        \fwrite($stream, $content);
        \fseek($stream, 0);

        return $this->streamFor($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->streamFor(Util::tryFopen($filename, $mode));
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return $this->streamFor($resource);
    }

    /**
     * Create a new stream based on the input type.
     *
     * @param null|bool|callable|float|int|\Iterator|resource|StreamInterface|string $resource Entity body data
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if the $resource arg is not valid
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function streamFor($resource): StreamInterface
    {
        if (\is_scalar($resource)) {
            return $this->createStreamForScalar($resource);
        }

        $type = \gettype($resource);

        if ($type === 'resource') {
            return new Stream($resource);
        }

        if ($type === 'object') {
            if ($resource instanceof StreamInterface) {
                return $resource;
            }

            if ($resource instanceof \Iterator) {
                return new PumpStream(static function () use ($resource) {
                    if (! $resource->valid()) {
                        return false;
                    }

                    $result = $resource->current();
                    $resource->next();

                    return $result;
                });
            }

            if (\method_exists($resource, '__toString')) {
                return $this->createStreamForScalar($resource->__toString());
            }
        }

        if ($type === 'NULL') {
            return new Stream(\fopen('php://temp', 'rb+'));
        }

        if (\is_callable($resource)) {
            return new PumpStream($resource);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . $type);
    }

    /**
     * Creates a stream for scalar types.
     *
     * @param bool|float|int|string $resource
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function createStreamForScalar($resource): StreamInterface
    {
        $stream = \fopen('php://temp', 'rb+');

        if ($resource !== '') {
            \fwrite($stream, $resource);
            \fseek($stream, 0);
        }

        return new Stream($stream);
    }
}
