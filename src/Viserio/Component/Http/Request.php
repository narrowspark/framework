<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;

class Request extends AbstractMessage implements RequestInterface, RequestMethodInterface
{
    public const METHOD_LINK    = 'LINK';
    public const METHOD_UNLINK  = 'UNLINK';

    /**
     * The request method.
     *
     * @var string
     */
    protected $method;

    /**
     * The request URI target (path + query string).
     *
     * @var string
     */
    protected $requestTarget;

    /**
     * The request URI object.
     *
     * @var null|\Psr\Http\Message\UriInterface
     */
    protected $uri;

    /**
     * Create a new request instance.
     *
     * @param null|string|UriInterface                               $uri     uri for the request
     * @param null|string                                            $method  http method for the request
     * @param array                                                  $headers headers for the message
     * @param null|\Psr\Http\Message\StreamInterface|resource|string $body    message body
     * @param string                                                 $version http protocol version
     */
    public function __construct(
        $uri,
        ?string $method = self::METHOD_GET,
        array $headers  = [],
        $body           = null,
        string $version = '1.1'
    ) {
        $this->method = $this->filterMethod($method);
        $this->uri    = $this->createUri($uri);
        $this->setHeaders($headers);
        $this->protocol = $version;

        if (! $this->hasHeader('Host') && $this->uri->getHost()) {
            $this->updateHostFromUri();
        }

        if ($body !== '' && $body !== null) {
            $this->stream = $this->createStream($body);
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
        if (\preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace.'
            );
        }

        $new                = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method): RequestInterface
    {
        $method = $this->filterMethod($method);

        $new         = clone $this;
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

        $new      = clone $this;
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
            if (\mb_strtolower($oldHeader) === 'host') {
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
     * @param null|string $method
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException on invalid HTTP method
     *
     * @return string
     */
    private function filterMethod(?string $method): string
    {
        if ($method === null) {
            return self::METHOD_GET;
        }

        if (! \preg_match("/^[!#$%&'*+.^_`|~0-9a-z-]+$/i", $method)) {
            throw new InvalidArgumentException(\sprintf(
                'Unsupported HTTP method [%s].',
                $method
            ));
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
     * @param null|string|UriInterface $uri
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
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

        throw new InvalidArgumentException(
            'Invalid URI provided; must be null, a string or a [\Psr\Http\Message\UriInterface] instance.'
        );
    }
}
