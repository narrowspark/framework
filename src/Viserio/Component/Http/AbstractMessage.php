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

/**
 * This class supports headers with numeric keys.
 * Note: technically, -1 is a valid header name.
 *
 *    RFC7230 defines it as following:
 *
 *    header-field = field-name ":" OWS field-value OWS
 *    field-name = token
 *    token = 1*tchar
 *    tchar = "!" / "#" / "$" / "%" / "&" / "'" / "*" / "+" / "-" / "." /
 *    "^" / "_" / "`" / "|" / "~" / DIGIT / ALPHA
 */
abstract class AbstractMessage implements MessageInterface
{
    protected string $protocolVersion = '1.1';

    /**
     * Map of all registered headers, as original name => array of values.
     *
     * @var array<int|string, mixed>
     */
    protected array $headers = [];

    /**
     * Map of lowercase header name => original name at registration.
     *
     * @var array<int|string, int|string>
     */
    protected array $headerNames = [];

    protected ?StreamInterface $stream;

    /**
     * A map of valid protocol versions.
     *
     * @var array<int|string, bool>
     */
    private static array $validProtocolVersions = [
        '1.0' => true,
        '1.1' => true,
        '2.0' => true,
        '2' => true,
    ];

    /**
     * Disable magic setter to ensure immutability.
     *
     * @param string $name  The property name
     * @param mixed  $value The property value
     *
     * @return void
     */
    public function __set($name, $value): void
    {
        // Do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
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
    public function withProtocolVersion($version)
    {
        $this->validateProtocolVersion($version);

        if ($this->protocolVersion === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param int|string $name case-insensitive header field name
     *
     * @return bool Returns true if any header names match the given header
     *              name using a case-insensitive string comparison. Returns false if
     *              no matching header name is found in the message.
     */
    public function hasHeader($name): bool
    {
        return \array_key_exists(! \is_int($name) ? \strtr($name, Util::UPPER_CASE, Util::LOWER_CASE) : $name, $this->headerNames);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param int|string $header case-insensitive header field name
     *
     * @return string[] An array of string values as provided for the given
     *                  header. If the header does not appear in the message, this method MUST
     *                  return an empty array.
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

        if (\count($value) === 0) {
            return '';
        }

        return \implode(',', $value);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param int|string      $header case-insensitive header field name
     * @param string|string[] $value  header value(s)
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException for invalid header names or values
     *
     * @return static
     */
    public function withHeader($header, $value)
    {
        $this->assertHeader($header);

        $value = $this->filterHeaderValue($value);

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
        $new->headers[$header] = $value;

        return $new;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param int|string      $header case-insensitive header field name to add
     * @param string|string[] $value  header value(s)
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException for invalid header names or values
     *
     * @return static
     */
    public function withAddedHeader($header, $value)
    {
        $this->assertHeader($header);

        if (! $this->hasHeader($header)) {
            return $this->withHeader($header, $value);
        }

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
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param int|string $header case-insensitive header field name to remove
     *
     * @return static
     */
    public function withoutHeader($header)
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
            $this->stream = new Stream('php://temp', ['mode' => 'r+b']);
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
     * Set validated headers.
     *
     * @param array<int|string, mixed> $headers
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
            $this->assertHeader($header);

            $value = $this->filterHeaderValue($value);

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
     * Filter array headers.
     *
     * @param array<int|string, mixed>|string $values
     *
     * @return array<int|string, mixed>
     */
    private function filterHeaderValue($values): array
    {
        if (! \is_array($values)) {
            $values = [$values];
        }

        if (\count($values) === 0) {
            throw new InvalidArgumentException('Invalid header value: must be a string or array of strings and cannot be an empty array.');
        }

        $values = \array_map(static function ($value): string {
            if ((! \is_numeric($value) && ! \is_string($value))) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }

            // @see http://tools.ietf.org/html/rfc7230#section-3.2
            HeaderSecurity::assertValid((string) $value);

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
        if (! \is_string($header) && ! \is_numeric($header)) {
            throw new InvalidArgumentException(\sprintf('Invalid header name type; expected string or integer; received [%s].', (\is_object($header) ? \get_class($header) : \gettype($header))));
        }

        if ($header === '') {
            throw new InvalidArgumentException('Header name can not be empty.');
        }

        HeaderSecurity::assertValidName($header);
    }
}
