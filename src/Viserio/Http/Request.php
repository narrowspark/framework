<?php

declare(strict_types=1);
namespace Viserio\Http;

use InvalidArgumentException;
use Psr\Http\Message\{
    RequestInterface,
    StreamInterface,
    UriInterface
};

class Request extends AbstractMessage implements RequestInterface
{
    protected static $validMethods = [
        'OPTIONS'  => true,
        'GET'      => true,
        'HEAD'     => true,
        'POST'     => true,
        'PUT'      => true,
        'DELETE'   => true,
        'TRACE'    => true,
        'CONNECT'  => true,
        'PATCH'    => true,
        'PROPFIND' => true,
    ];

    /**
     * Array of possible CSRF Header names
     *
     * @var array
     */
    protected static $csrfHeaderNames = [
        'X-CSRF-Token',
        'X-CSRFToken',
        'X-XSRF-TOKEN',
    ];

    /**
     * The request method
     *
     * @var string
     */
    protected $method;

    /**
     * The request URI target (path + query string)
     *
     * @var string
     */
    protected $requestTarget;

    /**
     * The request URI object
     *
     * @var \Psr\Http\Message\UriInterface|null
     */
    protected $uri;

    /**
     * Create a new request instance.
     *
     * @param null|string|UriInterface             $uri     URI for the request.
     * @param string|null                          $method  HTTP method for the request.
     * @param array                                $headers Headers for the message.
     * @param string|null|resource|StreamInterface $body    Message body.
     * @param string                               $version HTTP protocol version.
     */
    public function __construct(
        $uri,
        $method = 'GET',
        array $headers = [],
        $body = null,
        string $version = '1.1'
    ) {
        $this->method = $this->filterMethod($method);
        $this->uri = $this->createUri($uri);
        $this->setHeaders($headers);
        $this->protocol = $version;

        if (! $this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        if ($body !== '' && $body !== null) {
            $this->stream = Util::getStream($body);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        if ($target == '') {
            $target = '/';
        }

        if ($this->uri->getQuery() != '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace'
            );
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $method = $this->filterMethod($method);

        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if (! $preserveHost) {
            $new->updateHostFromUri();
        }

        return $new;
    }

    /**
     * Retrieve the host from the URI instance
     */
    private function updateHostFromUri()
    {
        $host = $this->uri->getHost();

        if ($host == '') {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        if (isset($this->headerNames['host'])) {
            $header = $this->headerNames['host'];
        } else {
            $header = 'Host';
            $this->headerNames['host'] = 'Host';
        }

        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headers = [$header => [$host]] + $this->headers;
    }

    /**
     * Validate the HTTP method
     *
     * @param null|string $method
     *
     * @throws InvalidArgumentException on invalid HTTP method.
     *
     * @return string
     */
    private function filterMethod($method): string
    {
        if ($method === null) {
            return 'GET';
        }

        $method = strtoupper($method);

        if (! is_string($method)) {
            throw new InvalidArgumentException(
                'The HTTP method must be a string'
            );
        }

        $method = strtoupper($method);

        if (! isset(static::$validMethods[$method])) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s"',
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
     * @throws InvalidArgumentException
     *
     * @return UriInterface
     */
    private function createUri($uri)
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }

        if (is_string($uri)) {
            return new Uri($uri);
        }

        if ($uri === null) {
            return new Uri();
        }

        throw new InvalidArgumentException(
            'Invalid URI provided; must be null, a string, or a Psr\Http\Message\UriInterface instance'
        );
    }
}
