<?php
namespace Viserio\Contracts\Cookie;

use DateTime;

interface Cookie
{
    /**
     * Returns the name
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the value
     *
     * @param string|null $value
     *
     * @return self
     */
    public function withValue($value);

    /**
     * Returns the value
     *
     * @return string|null
     */
    public function getValue();

    /**
     * Checks if there is a value
     *
     * @return bool
     */
    public function hasValue();

    /**
     * Sets the max age
     *
     * @param int|null $maxAge
     *
     * @return self
     */
    public function withMaxAge($maxAge);

    /**
     * Returns the max age
     *
     * @return int|null
     */
    public function getMaxAge();

    /**
     * Checks if there is a max age
     *
     * @return bool
     */
    public function hasMaxAge();

    /**
     * Sets both the max age and the expires attributes
     *
     * @param int|\DateTime|null $expiration
     *
     * @return self
     */
    public function withExpiration($expiration);

    /**
     * Sets the expires
     *
     * @param \DateTime $expires
     *
     * @return self
     */
    public function withExpires(DateTime $expires);

    /**
     * Returns the expiration time
     *
     * @return integer
     */
    public function getExpiresTime();

    /**
     * Checks if there is an expiration time
     *
     * @return bool
     */
    public function hasExpires();

    /**
     * Checks if the cookie is expired
     *
     * @return bool
     */
    public function isExpired();

    /**
     * Sets the domain
     *
     * @param string|null $domain
     *
     * @return self
     */
    public function withDomain($domain);

    /**
     * Returns the domain
     *
     * @return string|null
     */
    public function getDomain();

    /**
     * Checks if there is a domain
     *
     * @return bool
     */
    public function hasDomain();

    /**
     * Sets the path
     *
     * @param string|null $path
     *
     * @return self
     */
    public function withPath($path);

    /**
     * Returns the path
     *
     * @return string
     */
    public function getPath();

    /**
     * Sets the secure
     *
     * @param bool $secure
     *
     * @return self
     */
    public function withSecure($secure);

    /**
     * Checks if HTTPS is required
     *
     * @return bool
     */
    public function isSecure();

    /**
     * Sets the HTTP Only
     *
     * @param bool $httpOnly
     *
     * @return self
     */
    public function withHttpOnly($httpOnly);

    /**
     * Checks if it is HTTP-only
     *
     * @return bool
     */
    public function isHttpOnly();

    /**
     * It matches a path
     *
     * @param string $path
     *
     * @return bool
     *
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.4
     */
    public function matchPath($path);

    /**
     * Checks if it matches with another cookie
     *
     * @param \Viserio\Contracts\Cookie\Cookie $cookie
     *
     * @return bool
     */
    public function matchCookie(Cookie $cookie);

    /**
     * Matches a domain
     *
     * @param string $domain
     *
     * @return bool
     *
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.3
     */
    public function matchDomain($domain);
}
