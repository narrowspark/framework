<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Viserio\Contracts\HttpFactory\StreamFactory as StreamFactoryContract;
use Viserio\Http\Stream;

final class StreamFactory implements StreamFactoryContract
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
