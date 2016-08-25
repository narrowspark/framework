<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Viserio\Http\Stream\PumpStream;

final class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream($stream)
    {
        return new Stream(fopen('php://temp', 'r+'));
    }
}
