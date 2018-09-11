<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;

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
     * @var array<string,string>
     */
    protected $headers = [];

    /**
     * Map of lowercase header name => original name at registration.
     *
     * @var array<string,string>
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
     * @var array<string,int>
     */
    private static $validProtocolVersions = [
        '1.0' => true,
        '1.1' => true,
        '2.0' => true,
    ];

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

        return \is_array($value) ? $value : [$value];
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
    public function withHeader($header, $value)
    {
        if (! \is_string($header)) {
            throw new InvalidArgumentException(\sprintf(
                'Invalid header name type; expected string; received [%s]',
                (\is_object($header) ? \get_class($header) : \gettype($header))
            ));
        }

        HeaderSecurity::assertValidName($header);

        $header     = \trim($header);
        $normalized = \mb_strtolower($header);
        $new        = clone $this;

        // Remove the header lines.
        if ($new->hasHeader($header)) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }

        // Add the header line.
        $new->headerNames[$normalized] = $header;
        $new->headers[$header]         = $this->filterHeaderValue($value);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($header, $value): self
    {
        if (! \is_string($header)) {
            throw new InvalidArgumentException(\sprintf(
                'Invalid header name type; expected string; received [%s]',
                (\is_object($header) ? \get_class($header) : \gettype($header))
            ));
        }

        if (! $this->hasHeader($header)) {
            return $this->withHeader($header, $value);
        }

        HeaderSecurity::assertValidName($header);

        $header = \trim($header);
        $header = $this->headerNames[\mb_strtolower($header)];

        $new                   = clone $this;
        $value                 = $this->filterHeaderValue($value);
        $new->headers[$header] = \array_merge($this->headers[$header], $value);

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
        if ($this->stream === null) {
            $this->stream = new Stream(\fopen('php://temp', 'r+b'));
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
        if (\count($headers) === 0) {
            return;
        }

        $this->headerNames = $this->headers = [];

        foreach ($headers as $header => $value) {
            $value      = $this->filterHeaderValue($value);
            $normalized = \mb_strtolower($header);

            if (isset($this->headerNames[$normalized])) {
                $header                 = (string) $this->headerNames[$normalized];
                $this->headers[$header] += $value;
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
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if the $resource arg is not valid
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function createStream($body): StreamInterface
    {
        $type = \gettype($body);

        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (\is_string($body)) {
            $stream = \fopen('php://temp', 'r+b');

            if ($body !== '') {
                \fwrite($stream, $body);
                \fseek($stream, 0);
            }

            return new Stream($stream);
        }

        if ($type === 'NULL') {
            return new Stream(\fopen('php://temp', 'r+b'));
        }

        if ($type === 'resource') {
            return new Stream($body);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . \gettype($body));
    }

    /**
     * Validate the HTTP protocol version.
     *
     * @param string $version
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException on invalid HTTP protocol version
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
     * Filter array headers.
     *
     * @param array|string $values
     *
     * @return array
     */
    private function filterHeaderValue($values): array
    {
        if (! \is_array($values)) {
            $values = [$values];
        }

        if (\count($values) === 0 || ! $this->arrayContainsOnlyStrings($values)) {
            throw new InvalidArgumentException(
                'Invalid header value: must be a string or array of strings and cannot be an empty array.'
            );
        }

        $values = \array_map(function ($value) {
            // @see http://tools.ietf.org/html/rfc7230#section-3.2
            HeaderSecurity::assertValid($value);

            $value = (string) $value;

            // Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
            //
            // header-field = field-name ":" OWS field-value OWS
            // OWS          = *( SP / HTAB )
            //
            // @see https://tools.ietf.org/html/rfc7230#section-3.2.4
            return \trim($value, " \t");
        }, \array_values($values));

        return \array_map([HeaderSecurity::class, 'filter'], \array_values($values));
    }
}
