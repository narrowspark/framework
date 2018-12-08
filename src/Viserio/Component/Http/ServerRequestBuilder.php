<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Http\Exception\UnexpectedValueException;
use Viserio\Component\Http\Stream\CachingStream;
use Viserio\Component\Http\Stream\LazyOpenStream;

final class ServerRequestBuilder
{
    /**
     * Create a new server request from the current environment variables.
     * Defaults to a GET request to minimise the risk of an \InvalidArgumentException.
     * Includes the current request headers as supplied by the server.
     * Defaults to php://input for the request body.
     *
     * @throws \InvalidArgumentException if no valid method or URI can be determined
     */
    public function createFromGlobals(): ServerRequestInterface
    {
        $server = $_SERVER;

        if (! isset($server['REQUEST_METHOD'])) {
            $server['REQUEST_METHOD'] = 'GET';
        }

        return $this->createFromArray(
            $server,
            getallheaders(),
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
     * @param array                                                         $server  typically $_SERVER or similar structure
     * @param array                                                         $headers typically the output of getallheaders() or similar structure
     * @param array                                                         $cookie  typically $_COOKIE or similar structure
     * @param array                                                         $get     typically $_GET or similar structure
     * @param array                                                         $post    typically $_POST or similar structure
     * @param array                                                         $files   typically $_FILES or similar structure
     * @param null|\Psr\Http\Message\ServerRequestInterface|resource|string $body    Typically stdIn
     *
     * @throws \InvalidArgumentException if no valid method or URI can be determined
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function createFromArray(
        array $server,
        array $headers = [],
        array $cookie  = [],
        array $get     = [],
        array $post    = [],
        array $files   = [],
        $body          = null
    ): ServerRequestInterface {
        if (isset($server['SERVER_ADDR'])) {
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
     * @param array $server
     *
     * @return string
     */
    private function getMethodFromServer(array $server): string
    {
        if (! isset($server['REQUEST_METHOD'])) {
            throw new InvalidArgumentException('Cannot determine HTTP method.');
        }

        return $server['REQUEST_METHOD'];
    }

    /**
     * Return HTTP protocol version (X.Y).
     *
     * @param array $server
     *
     * @throws \UnexpectedValueException
     *
     * @return string
     */
    private function marshalProtocolVersion(array $server): string
    {
        if (! isset($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }

        if (! \preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
            throw new UnexpectedValueException(\sprintf(
                'Unrecognized protocol version [%s].',
                $server['SERVER_PROTOCOL']
            ));
        }

        return $matches['version'];
    }
}
