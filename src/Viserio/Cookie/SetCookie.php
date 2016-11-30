<?php
declare(strict_types=1);
namespace Viserio\Cookie;

use InvalidArgumentException;
use Viserio\Contracts\Cookie\Cookie as CookieContract;
use Viserio\Cookie\Traits\CookieValidatorTratis;

final class SetCookie extends AbstractCookie
{
    use CookieValidatorTratis;

    /**
     * @param string                             $name       The name of the cookie.
     * @param string|null                        $value      The value of the cookie.
     * @param int|string|\DateTimeInterface|null $expiration The time the cookie expires.
     * @param string                             $path       The path on the server in which the cookie will
     *                                                       be available on.
     * @param string|null                        $domain     The domain that the cookie is available to.
     * @param bool                               $secure     Whether the cookie should only be transmitted
     *                                                       over a secure HTTPS connection from the client.
     * @param bool                               $httpOnly   Whether the cookie will be made accessible only.
     *                                                       through the HTTP protocol.
     * @param string|bool                        $sameSite   Whether the cookie will be available for cross-site requests
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $name,
        string $value = null,
        $expiration = 0,
        $path = '/',
        $domain = null,
        bool $secure = false,
        bool $httpOnly = false,
        $sameSite = false
    ) {
        $this->validateName($name);
        $this->validateValue($value);

        $this->name = $name;
        $this->value = $value;
        $this->maxAge = is_int($expiration) ? $expiration : null;
        $this->expires = $this->normalizeExpires($expiration);
        $this->domain = $this->normalizeDomain($domain);
        $this->path = $this->normalizePath($path);
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $this->validateSameSite($sameSite);
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString()
    {
        $cookieStringParts = [];

        $cookieStringParts = $this->appendFormattedNameAndValuePartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedPathPartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedDomainPartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedMaxAgePartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedSecurePartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedHttpOnlyPartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedSameSitePartIfSet($cookieStringParts);

        return implode('; ', $cookieStringParts);
    }

    /**
     * {@inheritdoc}
     */
    public function withValue(string $value = null): CookieContract
    {
        $this->validateValue($value);

        $new = clone $this;
        $new->value = $value;

        return $new;
    }
}
