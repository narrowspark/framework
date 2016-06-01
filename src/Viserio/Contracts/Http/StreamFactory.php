<?php
namespace Viserio\Contracts\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

interface StreamFactory
{
    /**
     * Creates a new PSR-7 stream.
     *
     * @param string|resource|StreamInterface|null $body
     *
     * @throws InvalidArgumentException If the stream body is invalid.
     * @throws RuntimeException         If creating the stream from $body fails.
     *
     * @return StreamInterface
     */
    public function createStream($body = null): StreamInterface;
}
