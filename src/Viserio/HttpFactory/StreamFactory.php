<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Interop\Http\Factory\StreamFactoryInterface;
use Viserio\Http\Stream;
use Psr\Http\Message\StreamInterface;

final class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream($content = ''): StreamInterface
    {
        $stream = fopen('php://memory', 'r+');

        fwrite($stream, $content);
        fseek($stream, 0);

        return new Stream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile($file, $mode = 'r'): StreamInterface
    {
        $stream = fopen('php://temp', $mode);

        fwrite($stream, $file);
        fseek($stream, 0);

        return new Stream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
