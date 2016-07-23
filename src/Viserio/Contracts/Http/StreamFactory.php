<?php

declare(strict_types=1);
namespace Viserio\Contracts\Http;

use Psr\Http\Message\StreamInterface;

interface StreamFactory
{
    /**
     * Create a new stream with no content.
     *
     * The stream will be writable and seekable.
     *
     * @return StreamInterface
     */
    public function createStream(): StreamInterface;

    /**
     * Create a new stream from a callback.
     *
     * The stream will be read-only and not seekable.
     *
     * @param callable $callback
     *
     * @return StreamInterface
     */
    public function createStreamFromCallback(callable $callback): StreamInterface;

    /**
     * Create a new stream from a resource.
     *
     * @param resource $body
     *
     * @return StreamInterface
     */
    public function createStreamFromResource($body): StreamInterface;

    /**
     * Create a new stream from a string.
     *
     * A temporary resource will be created with the content of the string.
     * The resource will be writable and seekable.
     *
     * @param string $body
     *
     * @return StreamInterface
     */
    public function createStreamFromString(string $body): StreamInterface;
}
