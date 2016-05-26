<?php
namespace Viserio\Cookie;

use InvalidArgumentException;
use Viserio\Contracts\Cookie\Cookie as CookieContract;

final class Cookie extends AbstractCookie
{
    /**
     * @param string                 $name       The name of the cookie.
     * @param string|null            $value      The value of the cookie.
     * @param int|\DateTimeInterface $expiration The time the cookie expires.
     * @param string|null            $path       The path on the server in which the cookie will
     *                                           be available on.
     * @param string|null            $domain     The domain that the cookie is available to.
     * @param bool                   $secure     Whether the cookie should only be transmitted
     *                                           over a secure HTTPS connection from the client.
     * @param bool                   $httpOnly   Whether the cookie will be made accessible only.
     *                                           through the HTTP protocol.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $name,
        $value = null,
        $expiration = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {
        $this->validateName($name);
        $this->validateValue($value);

        $this->name     = $name;
        $this->value    = $value;
        $this->maxAge   = is_int($expiration) ? $expiration : null;
        $this->expires  = $this->normalizeExpires($expiration);
        $this->domain   = $this->normalizeDomain($domain);
        $this->path     = $this->normalizePath($path);
        $this->secure   = filter_var($secure, FILTER_VALIDATE_BOOLEAN);
        $this->httpOnly = filter_var($httpOnly, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Sets the value
     *
     * @param string|null $value
     *
     * @return self
     */
    public function withValue($value = null): CookieContract
    {
        $this->validateValue($value);

        $new = clone $this;
        $new->value = $value;

        return $new;
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString(): string
    {
        $cookieStringParts = [];

        $cookieStringParts = $this->appendFormattedNameAndValuePartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedPathPartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedDomainPartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedMaxAgePartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedSecurePartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedHttpOnlyPartIfSet($cookieStringParts);

        return implode('; ', $cookieStringParts);
    }

    /**
     * Validates the name attribute
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @link http://tools.ietf.org/search/rfc2616#section-2.2
     */
    private function validateName(string $name)
    {
        if (strlen($name) < 1) {
            throw new InvalidArgumentException('The name cannot be empty');
        }

        // Name attribute is a token as per spec in RFC 2616
        if (preg_match('/[\x00-\x20\x22\x28-\x29\x2c\x2f\x3a-\x40\x5b-\x5d\x7b\x7d\x7f]/', $name)) {
            throw new InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }
    }

    /**
     * Validates a value
     *
     * @param string|null $value
     *
     * @throws \InvalidArgumentException
     *
     * @link http://tools.ietf.org/html/rfc6265#section-4.1.1
     */
    private function validateValue(string $value = null)
    {
        if (isset($value)) {
            if (preg_match('/[^\x21\x23-\x2B\x2D-\x3A\x3C-\x5B\x5D-\x7E]/', $value)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The cookie value "%s" contains invalid characters.',
                        $value
                    )
                );
            }
        }
    }
}
