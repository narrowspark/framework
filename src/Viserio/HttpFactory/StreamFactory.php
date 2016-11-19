<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Interop\Http\Factory\StreamFactoryInterface;
use Viserio\Http\Stream;

final class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream($content = '')
    {
        $stream = fopen('php://memory', 'r+');

        fwrite($stream, $content);
        fseek($stream, 0);

        return new Stream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile($file, $mode = 'r')
    {
        $stream = fopen('php://temp', $mode);

        fwrite($stream, $file);
        fseek($stream, 0);

        return new Stream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource)
    {
        return new Stream($resource);
    }
}
