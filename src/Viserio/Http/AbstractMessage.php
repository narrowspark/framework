<?php
namespace Viserio\Http;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * Protocol version.
     *
     * @var string
     */
    protected $protocol = '1.1';

    /**
     * A map of valid protocol versions.
     *
     * @var array
     */
    protected static $validProtocolVersions = [
        '1.0' => true,
        '1.1' => true,
        '2.0' => true,
    ];

    /**
     * Map of all registered headers, as original name => array of values.
     *
     * @var array
     */
    private $headers = [];

    /**
     * Map of lowercase header name => original name at registration.
     *
     * @var array
     */
    private $headerNames = [];

    /** @var StreamInterface */
    private $stream;

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $this->validateProtocolVersion($version);

        if ($this->protocol === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($header)
    {
        return isset($this->headerNames[strtolower($header)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($header)
    {
        if (! $this->hasHeader($header)) {
            return [];
        }

        $header = strtolower($header);
        $header = $this->headerNames[$header];
        $value  = $this->headers[$header];
        $value  = is_array($value) ? $value : [$value];

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($header)
    {
        $value = $this->getHeader($name);

        if (empty($value)) {
            return '';
        }

        return implode(',', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($header, $value)
    {
        $this->checkHeader($header, $value);

        $header = trim($header);
        $normalized = strtolower($header);
        $new = clone $this;

        // Remove the header lines.
        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }

        // Add the header line.
        $new->headerNames[$normalized] = $header;
        $new->headers[$header] = $value;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($header, $value)
    {
        $this->checkHeader($header, $value);

        if (!$this->hasHeader($header)) {
            return $this->withHeader($header, $value);
        }

        $normalized = strtolower($header);
        $new = clone $this;

        if (isset($new->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            $new->headers[$header] = array_merge($this->headers[$header], $value);
        } else {
            $new->headerNames[$normalized] = $header;
            $new->headers[$header] = $value;
        }

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($header)
    {
        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $normalized = strtolower($header);
        $header = $this->headerNames[$normalized];
        $new = clone $this;

        unset($new->headers[$header], $new->headerNames[$normalized]);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        if (!$this->stream) {
            $this->stream = $this->getStream('');
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    /**
     * [setHeaders description]
     *
     * @param array $headers
     */
    private function setHeaders(array $headers)
    {
        $this->headerNames = $this->headers = [];

        foreach ($headers as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }

            $value = $this->trimHeaderValues($value);
            $normalized = strtolower($header);

            if (isset($this->headerNames[$normalized])) {
                $header = $this->headerNames[$normalized];
                $this->headers[$header] = array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }
    }

    /**
     * Validate the HTTP protocol version.
     *
     * @param string $version
     *
     * @throws InvalidArgumentException on invalid HTTP protocol version
     */
    private function validateProtocolVersion($version)
    {
        if (empty($version)) {
            throw new InvalidArgumentException(sprintf(
                'HTTP protocol version can not be empty'
            ));
        }

        if (!isset(self::$validProtocolVersions[$version])) {
            throw new InvalidArgumentException(
                'Invalid HTTP version. Must be one of: '
                . implode(', ', array_keys(self::$validProtocolVersions))
            );
        }
    }

    /**
     * Assert that the provided header values are valid.
     *
     * @see http://tools.ietf.org/html/rfc7230#section-3.2
     * @param string[] $values
     *
     * @throws InvalidArgumentException
     */
    private static function assertValidHeaderValue(array $values)
    {
        array_walk($values, __NAMESPACE__ . '\HeaderSecurity::assertValid');
    }

    /**
     * Create a new stream based on the input type.
     *
     * Options is an associative array that can contain the following keys:
     * - metadata: Array of custom metadata.
     * - size: Size of the stream.
     *
     * @param resource|string|null|int|float|bool|StreamInterface|callable $resource Entity body data
     * @param array                                                        $options  Additional options
     *
     * @return Stream
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     */
    private function getStream($resource = '', array $options = []): StreamInterface
    {
        if (is_scalar($resource)) {
            $stream = fopen('php://temp', 'r+');

            if ($resource !== '') {
                fwrite($stream, $resource);
                fseek($stream, 0);
            }

            return new Stream($stream, $options);
        }

        switch (gettype($resource)) {
            case 'resource':
                return new Stream($resource, $options);
            case 'object':
                if ($resource instanceof StreamInterface) {
                    return $resource;
                } elseif ($resource instanceof \Iterator) {
                    return new PumpStream(function () use ($resource) {
                        if (!$resource->valid()) {
                            return false;
                        }

                        $result = $resource->current();
                        $resource->next();

                        return $result;
                    }, $options);
                } elseif (method_exists($resource, '__toString')) {
                    return $this->getStream((string) $resource, $options);
                }

                break;
            case 'NULL':
                return new Stream(fopen('php://temp', 'r+'), $options);
        }

        if (is_callable($resource)) {
            return new PumpStream($resource, $options);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . gettype($resource));
    }

    /**
     * Check all header values and header name.
     *
     * @param string          $header
     * @param string|string[] $value
     *
     * @return array
     */
    private function checkHeader($header, $value): array
    {
        if (is_string($value)) {
            $value = [ $value ];
        }

        if (! is_array($value) || ! $this->arrayContainsOnlyStrings($value)) {
            throw new InvalidArgumentException(
                'Invalid header value; must be a string or array of strings'
            );
        }

        $header = trim($header);

        HeaderSecurity::assertValidName($header);
        self::assertValidHeaderValue($value);

        return $value;
    }

    /**
     * Test that an array contains only strings
     *
     * @param array $array
     *
     * @return bool
     */
    private function arrayContainsOnlyStrings(array $array): bool
    {
        // Test if a value is a string.
        $filterStringValue  = function(bool $carry, $item) {
            if (! is_string($item)) {
                return false;
            }
            return $carry;
        };

        return array_reduce($array, $filterStringValue, true);
    }
}
