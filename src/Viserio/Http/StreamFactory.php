<?php
namespace Viserio\Http;

use Psr\Http\Message\StreamInterface;
use Viserio\Contracts\Http\StreamFactory as StreamFactoryContract;

final class StreamFactory implements StreamFactoryContract
{
    public function createStream($body = null): StreamInterface
    {
        return Util::getStream($body);
    }
}
