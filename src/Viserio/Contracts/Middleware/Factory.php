<?php
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

interface FactoryInterface
{
    /**
     * Create a PSR-7 Request object
     *
     * @param UriInterface? $uri     The URI for the request
     * @param string        $method  The HTTTP Method for the request
     * @param array         $headers The parsed headers for the request
     * @param mixed         $body    The body for the request
     *
     * @return Psr\Http\Message\RequestInterface The generated request
     */
    public function createRequest(
        UriInterface $uri = null,
        string $method = '',
        array $headers = [],
        $body = null
    ): RequestInterface;

    /**
     * Create a PSR-7 ServerRequest object
     *
     * @param UriInterface? $uri     The URI for the request
     * @param string        $method  The HTTTP Method for the request
     * @param array         $headers The parsed headers for the request
     * @param mixed         $body    The body for the request
     *
     * @return Psr\Http\Message\ServerRequestInterface The generated request
     */
    public function createServerRequest(
        UriInterface $uri = null,
        string $method = '',
        array $headers = [],
        $body = null
    ): ServerRequestInterface;

    /**
     * Create a PSR-7 Response Object
     *
     * @param int   $status  The HTTP status code for the response
     * @param array $headers The parsed headers for the response
     * @param mixed $body    The body for the response
     *
     * @return Psr\Http\Message\ResponseInterface The generated response
     */
    public function createResponse(
        int $status = 200,
        array $headers = [],
        $body = null
    ): ResponseInterface;

    /**
     * Create a PSR-7 Stream Object.
     *
     * @param resource|string|null|int|float|bool|StreamInterface|callable $data
     *
     * @return Psr\Http\Message\StreamInterface
     */
    public function createStream($data = null): StreamInterface;

    /**
     * Create a PSR-7 Uri Object.
     *
     * @param string $uri
     *
     * @return Psr\Http\Message\UriInterface
     */
    public function createUri(string $uri = ''): UriInterface;

    /**
     * Create a PSR-7 Uploaded Object.
     *
     * @param StreamInterface|string|resource $data
     * @param int                             $size
     * @param int                             $error
     * @param string                          $clientFile
     * @param string                          $clientMediaType
     *
     * @return Psr\Http\Message\UploadedFileInterface
     */
    public function createUploadedFile(
        $data,
        int $size,
        int $error,
        string $clientFile = '',
        string $clientMediaType = ''
    ): UploadedFileInterface;
}
