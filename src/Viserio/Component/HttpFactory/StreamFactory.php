<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;

final class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream($content = ''): StreamInterface
    {
        $stream = \fopen('php://memory', 'r+');

        \fwrite($stream, $content);
        \fseek($stream, 0);

        return new Stream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile($filename, $mode = 'r'): StreamInterface
    {
        $resource = \fopen($filename, $mode);

        return new Stream($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
