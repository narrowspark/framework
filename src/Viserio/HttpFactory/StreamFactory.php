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
    public function createStream($resource)
    {
        if (gettype($resource) === 'resource') {
            return new Stream($resource);
        }

        $stream = fopen('php://temp', 'r+');

        if ($resource !== '') {
            fwrite($stream, $resource);
            fseek($stream, 0);
        }
        return new Stream($stream);
    }
}
