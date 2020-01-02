<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Http;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Http\Stream\CachingStream;
use Viserio\Component\Http\Stream\LazyOpenStream;
use Viserio\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Contract\Http\Exception\UnexpectedValueException;

final class ServerRequestBuilder
{
    /**
     * Create a new server request from the current environment variables.
     * Defaults to a GET request to minimise the risk of an \InvalidArgumentException.
     * Includes the current request headers as supplied by the server.
     * Defaults to php://input for the request body.
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException if no valid method or URI can be determined
     */
    public function createFromGlobals(): ServerRequestInterface
    {
        $server = $_SERVER;

        if (! \array_key_exists('REQUEST_METHOD', $server)) {
            $server['REQUEST_METHOD'] = 'GET';
        }

        return $this->createFromArray(
            $server,
            Util::getAllHeaders($server),
            $_COOKIE,
            $_GET,
            $_POST,
            $_FILES,
            new CachingStream(new LazyOpenStream('php://input', 'r+'))
        );
    }

    /**
     * Create a new server request from a set of arrays.
     *
     * @param array<int|string, mixed>                               $server  typically $_SERVER or similar structure
     * @param array<int|string, mixed>                               $headers typically the output of getallheaders() or similar structure
     * @param array<int|string, mixed>                               $cookie  typically $_COOKIE or similar structure
     * @param array<int|string, mixed>                               $get     typically $_GET or similar structure
     * @param array<int|string, mixed>                               $post    typically $_POST or similar structure
     * @param array<int|string, mixed>                               $files   typically $_FILES or similar structure
     * @param null|\Psr\Http\Message\StreamInterface|resource|string $body    Typically stdIn
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException if no valid method or URI can be determined
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function createFromArray(
        array $server,
        array $headers = [],
        array $cookie = [],
        array $get = [],
        array $post = [],
        array $files = [],
        $body = null
    ): ServerRequestInterface {
        if (\array_key_exists('SERVER_ADDR', $server)) {
            $server['SERVER_ADDR'] = \str_replace('Server IP: ', '', $server['SERVER_ADDR']);
        }

        $serverRequest = new ServerRequest(
            Uri::createFromServer($server),
            $this->getMethodFromServer($server),
            $headers,
            $body,
            $this->marshalProtocolVersion($server),
            $server
        );

        return $serverRequest
            ->withCookieParams($cookie)
            ->withQueryParams($get)
            ->withParsedBody($post)
            ->withUploadedFiles(Util::normalizeFiles($files));
    }

    /**
     * @param array<int|string, mixed> $server
     *
     * @return string
     */
    private function getMethodFromServer(array $server): string
    {
        if (! \array_key_exists('REQUEST_METHOD', $server)) {
            throw new InvalidArgumentException('Cannot determine HTTP method.');
        }

        return $server['REQUEST_METHOD'];
    }

    /**
     * Return HTTP protocol version (X.Y).
     *
     * @param array<int|string, mixed> $server
     *
     * @throws \Viserio\Contract\Http\Exception\UnexpectedValueException
     *
     * @return string
     */
    private function marshalProtocolVersion(array $server): string
    {
        if (! \array_key_exists('SERVER_PROTOCOL', $server)) {
            return '1.1';
        }

        if (\preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches) !== 1) {
            throw new UnexpectedValueException(\sprintf('Unrecognized protocol version [%s].', $server['SERVER_PROTOCOL']));
        }

        return $matches['version'];
    }
}
