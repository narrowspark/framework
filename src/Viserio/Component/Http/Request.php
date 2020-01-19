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

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

class Request extends AbstractMessage implements RequestInterface, RequestMethodInterface
{
    /** @var string */
    public const METHOD_LINK = 'LINK';

    /** @var string */
    public const METHOD_UNLINK = 'UNLINK';

    protected string $method;

    /**
     * The request URI target (path + query string).
     *
     * @var null|string
     */
    protected ?string $requestTarget;

    protected UriInterface $uri;

    /**
     * Create a new request instance.
     *
     * @param null|string|UriInterface                               $uri     uri for the request
     * @param string                                                 $method  http method for the request
     * @param array<int|string, mixed>                               $headers headers for the message
     * @param null|\Psr\Http\Message\StreamInterface|resource|string $body    message body
     * @param string                                                 $version http protocol version
     */
    public function __construct(
        $uri,
        string $method = self::METHOD_GET,
        array $headers = [],
        $body = null,
        string $version = '1.1'
    ) {
        $this->requestTarget = null;
        $this->stream = null;

        $this->method = $this->filterMethod($method);
        $this->uri = $this->createUri($uri);
        $this->setHeaders($headers);
        $this->protocolVersion = $version;

        // per PSR-7: attempt to set the Host header from a provided URI if no
        // Host header is provided

        if (! $this->hasHeader('Host') && $this->uri->getHost() !== '') {
            $this->updateHostFromUri();
        }

        if ($body !== '' && $body !== null) {
            $this->stream = Util::createStreamFor($body);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        if ($target === '') {
            $target = '/';
        }

        if ($this->uri->getQuery() !== '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget): RequestInterface
    {
        if (\preg_match('#\s#', $requestTarget) === 1) {
            throw new InvalidArgumentException('Invalid request target provided; cannot contain whitespace.');
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method): RequestInterface
    {
        if (! \is_string($method)) {
            throw new InvalidArgumentException('Method must be a string.');
        }

        $method = $this->filterMethod($method);

        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        if ($this->uri === $uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if ($preserveHost && $this->hasHeader('Host')) {
            return $new;
        }

        $new->updateHostFromUri();

        return $new;
    }

    /**
     * Retrieve the host from the URI instance.
     *
     * @return void
     */
    private function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();

        if ($host === '') {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        $this->headerNames['host'] = 'Host';

        // Remove an existing host header if present, regardless of current
        // de-normalization of the header name.
        // @see https://github.com/zendframework/zend-diactoros/issues/91
        foreach (\array_keys($this->headers) as $oldHeader) {
            if (\strtr((string) $oldHeader, Util::UPPER_CASE, Util::LOWER_CASE) === 'host') {
                unset($this->headers[$oldHeader]);
            }
        }

        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headers = ['Host' => [$host]] + $this->headers;
    }

    /**
     * Validate the HTTP method.
     *
     * @param string $method
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException on invalid HTTP method
     *
     * @return string
     */
    private function filterMethod(string $method): string
    {
        if (\preg_match("/^[!#$%&'*+.^_`|~0-9a-z-]+$/i", $method) !== 1) {
            throw new InvalidArgumentException(\sprintf('Unsupported HTTP method [%s].', $method));
        }

        return $method;
    }

    /**
     * Create and return a URI instance.
     *
     * If `$uri` is a already a `UriInterface` instance, returns it.
     *
     * If `$uri` is a string, passes it to the `Uri` constructor to return an
     * instance.
     *
     * If `$uri is null, creates and returns an empty `Uri` instance.
     *
     * Otherwise, it raises an exception.
     *
     * @param mixed $uri
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException
     *
     * @return \Psr\Http\Message\UriInterface
     */
    private function createUri($uri): UriInterface
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }

        if (\is_string($uri)) {
            return Uri::createFromString($uri);
        }

        if ($uri === null) {
            return Uri::createFromString();
        }

        throw new InvalidArgumentException('Invalid URI provided; must be null, a string or a [\Psr\Http\Message\UriInterface] instance.');
    }
}
