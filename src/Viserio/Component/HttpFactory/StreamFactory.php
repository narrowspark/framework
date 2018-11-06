<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Util;

final class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = \fopen('php://memory', 'r+b');

        \fwrite($stream, $content);
        \fseek($stream, 0);

        return Util::createStreamFor($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return Util::createStreamFor(Util::tryFopen($filename, $mode));
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return Util::createStreamFor($resource);
    }
}
