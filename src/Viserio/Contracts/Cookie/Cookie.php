<?php
namespace Viserio\Contracts\Cookie;

use DateTimeInterface;

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
     * @return boolean
     */
    public function hasValue();

    /**
     * Sets the max age
     *
     * @param integer|null $maxAge
     *
     * @return self
     */
    public function withMaxAge($maxAge);

    /**
     * Returns the max age
     *
     * @return integer|null
     */
    public function getMaxAge();

    /**
     * Checks if there is a max age
     *
     * @return boolean
     */
    public function hasMaxAge();

    /**
     * Sets both the max age and the expires attributes
     *
     * @param integer|\DateTimeInterface|null $expiration
     *
     * @return self
     */
    public function withExpiration($expiration);

    /**
     * Sets the expires
     *
     * @param \DateTimeInterface|null $expires
     *
     * @return self
     */
    public function withExpires(DateTimeInterface $expires);

    /**
     * Returns the expiration time
     *
     * @return \DateTimeInterface|null
     */
    public function getExpiresTime();

    /**
     * Checks if there is an expiration time
     *
     * @return boolean
     */
    public function hasExpires();

    /**
     * Checks if the cookie is expired
     *
     * @return boolean
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
     * @return boolean
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
     * @param boolean $secure
     *
     * @return self
     */
    public function withSecure($secure);

    /**
     * Checks if HTTPS is required
     *
     * @return boolean
     */
    public function isSecure();

    /**
     * Sets the HTTP Only
     *
     * @param boolean $httpOnly
     *
     * @return self
     */
    public function withHttpOnly($httpOnly);

    /**
     * Checks if it is HTTP-only
     *
     * @return boolean
     */
    public function isHttpOnly();

    /**
     * It matches a path
     *
     * @param string $path
     *
     * @return boolean
     *
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.4
     */
    public function matchPath($path);

    /**
     * Checks if it matches with another cookie
     *
     * @param \Viserio\Contracts\Cookie\Cookie $cookie
     *
     * @return boolean
     */
    public function matchCookie(Cookie $cookie);

    /**
     * Matches a domain
     *
     * @param string $domain
     *
     * @return boolean
     *
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.3
     */
    public function matchDomain($domain);
}
