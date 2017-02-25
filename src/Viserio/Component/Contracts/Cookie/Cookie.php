<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Cookie;

interface Cookie
{
    /**
     * Const for samesite.
     */
    public const SAMESITE_STRICT = 'strict';
    public const SAMESITE_LAX    = 'lax';

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Sets the value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function withValue(string $value = ''): Cookie;

    /**
     * Returns the value.
     *
     * @return string|null
     */
    public function getValue();

    /**
     * Checks if there is a value.
     *
     * @return bool
     */
    public function hasValue(): bool;

    /**
     * Sets the max age.
     *
     * @param int|null $maxAge
     *
     * @return $this
     */
    public function withMaxAge(int $maxAge = null): Cookie;

    /**
     * Returns the max age.
     *
     * @return int|null
     */
    public function getMaxAge();

    /**
     * Checks if there is a max age.
     *
     * @return bool
     */
    public function hasMaxAge(): bool;

    /**
     * Sets the expires.
     *
     * @param int|string|\DateTimeInterface|null $expires
     *
     * @return $this
     */
    public function withExpires($expires): Cookie;

    /**
     * Returns the expiration time.
     *
     * @return null|int
     */
    public function getExpiresTime();

    /**
     * Checks if there is an expiration time.
     *
     * @return bool
     */
    public function hasExpires(): bool;

    /**
     * Checks if the cookie is expired.
     *
     * @return bool
     */
    public function isExpired(): bool;

    /**
     * Sets the domain.
     *
     * @param string|null $domain
     *
     * @return $this
     */
    public function withDomain(string $domain = null): Cookie;

    /**
     * Returns the domain.
     *
     * @return string|null
     */
    public function getDomain();

    /**
     * Checks if there is a domain.
     *
     * @return bool
     */
    public function hasDomain(): bool;

    /**
     * Sets the path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function withPath(string $path = '/'): Cookie;

    /**
     * Returns the path.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Sets the secure.
     *
     * @param bool $secure
     *
     * @return $this
     */
    public function withSecure(bool $secure): Cookie;

    /**
     * Checks if HTTPS is required.
     *
     * @return bool
     */
    public function isSecure(): bool;

    /**
     * Sets the HTTP Only.
     *
     * @param bool $httpOnly
     *
     * @return $this
     */
    public function withHttpOnly(bool $httpOnly): Cookie;

    /**
     * Checks if it is HTTP-only.
     *
     * @return bool
     */
    public function isHttpOnly(): bool;

    /**
     * Whether the cookie will be available for cross-site requests.
     *
     * @param string|bool $sameSite
     *
     * @return $this
     */
    public function withSameSite($sameSite): Cookie;

    /**
     * Checks if the cookie value should be sent with a SameSite attribute.
     *
     * @return bool
     */
    public function isSameSite(): bool;

    /**
     * Gets the SameSite attribute.
     *
     * @return string|bool|null
     */
    public function getSameSite();

    /**
     * It matches a path.
     *
     * @param string $path
     *
     * @return bool
     *
     * @link http://tools.ietf.org/html/rfc6265#section-5.1.4
     */
    public function matchPath(string $path): bool;

    /**
     * Checks if it matches with another cookie.
     *
     * @param \Viserio\Component\Contracts\Cookie\Cookie $cookie
     *
     * @return bool
     */
    public function matchCookie(Cookie $cookie): bool;

    /**
     * Matches a domain.
     *
     * @param string $domain
     *
     * @return bool
     *
     * @link http://tools.ietf.org/html/rfc6265#section-5.1.3
     */
    public function matchDomain(string $domain): bool;
}
