<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Contracts\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contracts\Http\Exception\UnexpectedValueException;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * Protocol version.
     *
     * @var string
     */
    protected $protocol = '1.1';

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
     * A stream instance.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $stream;

    /**
     * A map of valid protocol versions.
     *
     * @var array
     */
    private static $validProtocolVersions = [
        '1.0' => true,
        '1.1' => true,
        '2.0' => true,
    ];

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version): self
    {
        $this->validateProtocolVersion($version);

        if ($this->protocol === $version) {
            return $this;
        }

        $new           = clone $this;
        $new->protocol = $version;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($header): bool
    {
        return isset($this->headerNames[\mb_strtolower($header)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($header): array
    {
        if (! $this->hasHeader($header)) {
            return [];
        }

        $header = \mb_strtolower($header);
        $header = $this->headerNames[$header];
        $value  = $this->headers[$header];
        $value  = \is_array($value) ? $value : [$value];

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name): string
    {
        $value = $this->getHeader($name);

        if (empty($value)) {
            return '';
        }

        return \implode(',', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($header, $value): self
    {
        $value = $this->checkHeaderData($header, $value);

        $value      = $this->trimHeaderValues($value);
        $header     = \trim($header);
        $normalized = \mb_strtolower($header);
        $new        = clone $this;

        // Remove the header lines.
        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }

        // Add the header line.
        $new->headerNames[$normalized] = $header;
        $new->headers[$header]         = $value;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($header, $value): self
    {
        $value      = $this->checkHeaderData($header, $value);
        $normalized = \mb_strtolower($header);
        $new        = clone $this;

        if (isset($new->headerNames[$normalized])) {
            $header                = $this->headerNames[$normalized];
            $new->headers[$header] = \array_merge($this->headers[$header], $value);
        } else {
            $new->headerNames[$normalized] = $header;
            $new->headers[$header]         = $value;
        }

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($header): self
    {
        $normalized = \mb_strtolower($header);

        if (! isset($this->headerNames[$normalized])) {
            return $this;
        }

        $header = $this->headerNames[$normalized];
        $new    = clone $this;

        unset($new->headers[$header], $new->headerNames[$normalized]);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        if (! $this->stream) {
            $this->stream = new Stream(\fopen('php://temp', 'rb+'));
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): self
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new         = clone $this;
        $new->stream = $body;

        return $new;
    }

    /**
     * Set validated headers.
     *
     * @param array $headers
     *
     * @return void
     */
    protected function setHeaders(array $headers): void
    {
        if (empty($headers)) {
            return;
        }

        $this->headerNames = $this->headers = [];

        foreach ($headers as $header => $value) {
            $value      = $this->trimHeaderValues($this->filterHeaderValue((array) $value));
            $normalized = \mb_strtolower($header);

            if (isset($this->headerNames[$normalized])) {
                $header                 = $this->headerNames[$normalized];
                $this->headers[$header] = \array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header]         = $value;
            }
        }
    }

    /**
     * Create a new stream based on the input type.
     *
     * @param null|\Psr\Http\Message\StreamInterface|resource|string $body
     *
     * @throws \Viserio\Component\Contracts\Http\Exception\InvalidArgumentException if the $resource arg is not valid
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function createStream($body): StreamInterface
    {
        $type = \gettype($body);

        if ($body instanceof StreamInterface) {
            return $body;
        } elseif (\is_string($body)) {
            $stream = \fopen('php://temp', 'rb+');

            if ($body !== '') {
                \fwrite($stream, $body);
                \fseek($stream, 0);
            }

            return new Stream($stream);
        } elseif ($type === 'NULL') {
            return new Stream(\fopen('php://temp', 'rb+'));
        } elseif ($type === 'resource') {
            return new Stream($body);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . \gettype($body));
    }

    /**
     * Validate the HTTP protocol version.
     *
     * @param string $version
     *
     * @throws \Viserio\Component\Contracts\Http\Exception\InvalidArgumentException on invalid HTTP protocol version
     *
     * @return void
     */
    private function validateProtocolVersion(string $version): void
    {
        if ($version === '') {
            throw new InvalidArgumentException(\sprintf(
                'HTTP protocol version can not be empty'
            ));
        }

        if (! isset(self::$validProtocolVersions[$version])) {
            throw new InvalidArgumentException(
                'Invalid HTTP version. Must be one of: '
                . \implode(', ', \array_keys(self::$validProtocolVersions))
            );
        }
    }

    /**
     * Check all header values and header name.
     *
     * @param string       $header
     * @param array|string $value
     *
     * @throws \Viserio\Component\Contracts\Http\Exception\UnexpectedValueException
     *
     * @return array
     */
    private function checkHeaderData(string $header, $value): array
    {
        if (\is_string($value)) {
            $value = [$value];
        }

        if (! $this->arrayContainsOnlyStrings($value)) {
            throw new UnexpectedValueException(
                'Invalid header value; must be a string or array of strings'
            );
        }

        HeaderSecurity::assertValidName(\trim($header));

        $this->assertValidHeaderValue($value);

        $value = $this->trimHeaderValues($value);

        return $value;
    }

    /**
     * Test that an array contains only strings.
     *
     * @param array $array
     *
     * @return bool
     */
    private function arrayContainsOnlyStrings(array $array): bool
    {
        // Test if a value is a string.
        $filterStringValue = function (bool $carry, $item) {
            if (! \is_string($item)) {
                return false;
            }

            return $carry;
        };

        return \array_reduce($array, $filterStringValue, true);
    }

    /**
     * Assert that the provided header values are valid.
     *
     * @see http://tools.ietf.org/html/rfc7230#section-3.2
     *
     * @param array $values
     *
     * @throws \Viserio\Component\Contracts\Http\Exception\UnexpectedValueException
     *
     * @return void
     */
    private function assertValidHeaderValue(array $values): void
    {
        \array_walk($values, __NAMESPACE__ . '\HeaderSecurity::assertValid');
    }

    /**
     * Filter array headers.
     *
     * @param array $values
     *
     * @return array
     */
    private function filterHeaderValue(array $values): array
    {
        $values = \array_filter($values, function ($value) {
            return null !== $value;
        });

        return \array_map([HeaderSecurity::class, 'filter'], \array_values($values));
    }

    /**
     * Trims whitespace from the header values.
     *
     * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
     *
     * header-field = field-name ":" OWS field-value OWS
     * OWS          = *( SP / HTAB )
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     *
     * @param array $values Header values
     *
     * @return array Trimmed header values
     */
    private function trimHeaderValues(array $values): array
    {
        return \array_map(function ($value) {
            return \trim($value, " \t");
        }, $values);
    }
}
