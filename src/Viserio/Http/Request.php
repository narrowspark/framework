<?php
namespace Viserio\Http;

use InvalidArgumentException;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends AbstractMessage implements RequestInterface
{
    /** @var string */
    private $method;

    /** @var null|string */
    private $requestTarget;

    /** @var null|UriInterface */
    private $uri;

    /**
     * @param string $method HTTP method for the request.
     * @param string|UriInterface $uri URI for the request.
     * @param array $headers Headers for the message.
     * @param string|null|resource|StreamInterface $body Message body.
     * @param string $protocolVersion HTTP protocol version.
     */
    public function __construct(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        string $protocolVersion = '1.1'
    ) {
        if (!($uri instanceof UriInterface)) {
            $uri = new Uri($uri);
        }

        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->setHeaders($headers);
        $this->protocol = $protocolVersion;

        if (!$this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        if ($body != '') {
            $this->stream = til::getStream($body);
        }
    }

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

    public function getMethod()
    {
        return $this->method;
    }

    public function withMethod($method)
    {
        $new = clone $this;
        $new->method = strtoupper($method);

        return $new;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost) {
            $new->updateHostFromUri();
        }

        return $new;
    }

    public function withHeader($header, $value)
    {
        /** @var Request $newInstance */
        return parent::withHeader($header, $value);
    }

    private function updateHostFromUri()
    {
        $host = $this->uri->getHost();

        if ($host == '') {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headerLines = ['Host' => [$host]] + $this->headerLines;
        $this->headers = ['host' => [$host]] + $this->headers;
    }
}
