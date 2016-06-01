<?php
namespace Viserio\Contracts\Http;

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;
use RuntimeException;

interface StreamFactory
{
    /**
     * Creates a new PSR-7 stream.
     *
     * @param string|resource|StreamInterface|null $body
     *
     * @return StreamInterface
     *
     * @throws InvalidArgumentException If the stream body is invalid.
     * @throws RuntimeException         If creating the stream from $body fails.
     */
    public function createStream($body = null): StreamInterface;
}
