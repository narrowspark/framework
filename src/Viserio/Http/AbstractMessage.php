<?php
declare(strict_types=1);
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
    protected $headers = [];

    /**
     * Map of lowercase header name => original name at registration.
     *
     * @var array
     */
    protected $headerNames = [];

    /**
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $stream;

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
        $value = $this->headers[$header];
        $value = is_array($value) ? $value : [$value];

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
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
        $value = $this->checkHeaderData($header, $value);

        $value = $this->trimHeaderValues($value);
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
        $value = $this->checkHeaderData($header, $value);
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
        $normalized = strtolower($header);

        if (! isset($this->headerNames[$normalized])) {
            return $this;
        }

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
        if (! $this->stream) {
            $this->stream = new Stream(fopen('php://temp', 'r+'));
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
     * @param array $headers
     */
    protected function setHeaders(array $headers)
    {
        if (empty($headers)) {
            return;
        }

        $this->headerNames = $this->headers = [];

        foreach ($headers as $header => $value) {
            if (! is_array($value)) {
                $value = [$value];
            }

            $value = $this->trimHeaderValues($this->filterHeaderValue($value));
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
     * Create a new stream based on the input type.
     *
     * @param string|null|resource|\Psr\Http\Message\StreamInterface $body
     *
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function createStream($body): StreamInterface
    {
        $type = gettype($body);

        if ($body instanceof StreamInterface) {
            return $body;
        } elseif (is_string($body)) {
            $stream = fopen('php://temp', 'r+');

            if ($body !== '') {
                fwrite($stream, $body);
                fseek($stream, 0);
            }

            return new Stream($stream);
        } elseif ($type === 'NULL') {
            return new Stream(fopen('php://temp', 'r+'));
        } elseif ($type === 'resource') {
            return new Stream($body);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . gettype($body));
    }

    /**
     * Validate the HTTP protocol version.
     *
     * @param string $version
     *
     * @throws InvalidArgumentException on invalid HTTP protocol version
     */
    private function validateProtocolVersion(string $version)
    {
        if (empty($version)) {
            throw new InvalidArgumentException(sprintf(
                'HTTP protocol version can not be empty'
            ));
        }

        if (! isset(self::$validProtocolVersions[$version])) {
            throw new InvalidArgumentException(
                'Invalid HTTP version. Must be one of: '
                . implode(', ', array_keys(self::$validProtocolVersions))
            );
        }
    }

    /**
     * Check all header values and header name.
     *
     * @param string          $header
     * @param string|string[] $value
     *
     * @return array
     */
    private function checkHeaderData($header, $value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (! $this->arrayContainsOnlyStrings($value)) {
            throw new InvalidArgumentException(
                'Invalid header value; must be a string or array of strings'
            );
        }

        HeaderSecurity::assertValidName(trim($header));
        $this->assertValidHeaderValue($value);

        $value = $this->trimHeaderValues($value);

        return $value;
    }

    /**
     * Test that an array contains only strings
     *
     * @param string[] $array
     *
     * @return bool
     */
    private function arrayContainsOnlyStrings(array $array): bool
    {
        // Test if a value is a string.
        $filterStringValue = function (bool $carry, $item) {
            if (! is_string($item)) {
                return false;
            }

            return $carry;
        };

        return array_reduce($array, $filterStringValue, true);
    }

    /**
     * Ensure header names and values are valid.
     *
     * @param array $headers
     *
     * @throws InvalidArgumentException
     */
    private function assertHeaders(array $headers)
    {
        foreach ($headers as $name => $headerValues) {
            HeaderSecurity::assertValidName($name);
            $this->assertValidHeaderValue($headerValues);
        }
    }

    /**
     * Assert that the provided header values are valid.
     *
     * @see http://tools.ietf.org/html/rfc7230#section-3.2
     *
     * @param string[] $values
     *
     * @throws InvalidArgumentException
     */
    private function assertValidHeaderValue(array $values)
    {
        array_walk($values, __NAMESPACE__ . '\HeaderSecurity::assertValid');
    }

    /**
     * @param array $values
     */
    private function filterHeaderValue(array $values)
    {
        return array_map([HeaderSecurity::class, 'filter'], $values);
    }

    /**
     * Trims whitespace from the header values.
     *
     * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
     *
     * header-field = field-name ":" OWS field-value OWS
     * OWS          = *( SP / HTAB )
     *
     * @param string[] $values Header values
     *
     * @return string[] Trimmed header values
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function trimHeaderValues(array $values)
    {
        return array_map(function ($value) {
            return trim($value, " \t");
        }, $values);
    }
}
