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

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

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
     * Set validated headers.
     *
     * @param array<string, int> $headers
     *
     * @return void
     */
    protected function setHeaders(array $headers): void
    {
        if (\count($headers) === 0) {
            return;
        }

        $this->headerNames = $this->headers = [];

        // Numeric array keys are converted to int by PHP but having a header name '123' is not forbidden by the spec
        // and also allowed in withHeader().
        foreach ($headers as $header => $value) {
            $value = $this->filterHeaderValue($value);

            $this->assertHeader($header);

            $normalized = $header;

            if (! \is_int($header)) {
                $normalized = \strtr($header, Util::UPPER_CASE, Util::LOWER_CASE);
            }

            if (\array_key_exists($normalized, $this->headerNames)) {
                $header = (string) $this->headerNames[$normalized];
                $this->headers[$header] += $value;
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }
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

        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($header): bool
    {
        return \array_key_exists(! \is_int($header) ? \strtr($header, Util::UPPER_CASE, Util::LOWER_CASE) : $header, $this->headerNames);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($header): array
    {
        if (! $this->hasHeader($header)) {
            return [];
        }

        $name = $header;

        if (! \is_int($header)) {
            $name = \strtr($header, Util::UPPER_CASE, Util::LOWER_CASE);
        }

        $header = $this->headerNames[$name];
        $value = $this->headers[$header];

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
        $this->assertHeader($header);

        HeaderSecurity::assertValidName($header);

        $normalized = $header;

        if (! \is_int($header)) {
            $header = \trim($header);
            $normalized = \strtr($header, Util::UPPER_CASE, Util::LOWER_CASE);
        }

        $new = clone $this;

        // Remove the header lines.
        if ($new->hasHeader($header)) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }

        // Add the header line.
        $new->headerNames[$normalized] = $header;
        $new->headers[$header] = $this->filterHeaderValue($value);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($header, $value): self
    {
        $this->assertHeader($header);

        if (! $this->hasHeader($header)) {
            return $this->withHeader($header, $value);
        }

        HeaderSecurity::assertValidName($header);

        $name = $header;

        if (! \is_int($header)) {
            $name = \strtr(\trim($header), Util::UPPER_CASE, Util::LOWER_CASE);
        }

        $header = $this->headerNames[$name];

        $new = clone $this;
        $value = $this->filterHeaderValue($value);
        $new->headers[$header] = \array_merge($this->headers[$header], $value);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($header): self
    {
        $normalized = $header;

        if (! \is_int($header)) {
            $normalized = \strtr($header, Util::UPPER_CASE, Util::LOWER_CASE);
        }

        if (! \array_key_exists($normalized, $this->headerNames)) {
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
    public function getBody(): StreamInterface
    {
        if ($this->stream === null) {
            $this->stream = new Stream(Util::tryFopen('php://temp', 'r+b'));
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

        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    /**
     * Validate the HTTP protocol version.
     *
     * @param string $version
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException on invalid HTTP protocol version
     *
     * @return void
     */
    private function validateProtocolVersion(string $version): void
    {
        if ($version === '') {
            throw new InvalidArgumentException('HTTP protocol version can not be empty.');
        }

        if (! \array_key_exists($version, self::$validProtocolVersions)) {
            throw new InvalidArgumentException(\sprintf('Invalid HTTP version. Must be one of: [%s].', \implode(', ', \array_keys(self::$validProtocolVersions))));
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
        $filterStringValue = static function (bool $carry, $item) {
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
            throw new InvalidArgumentException('Invalid header value: must be a string or array of strings and cannot be an empty array.');
        }

        $values = \array_map(static function ($value) {
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

    /**
     * Assert given header.
     *
     * @param mixed $header
     *
     * @return void
     */
    private function assertHeader($header): void
    {
        if (! \is_string($header) && ! \is_int($header)) {
            throw new InvalidArgumentException(\sprintf('Invalid header name type; expected string or integer; received [%s].', (\is_object($header) ? \get_class($header) : \gettype($header))));
        }

        if ($header === '') {
            throw new InvalidArgumentException('Header name can not be empty.');
        }
    }
}
