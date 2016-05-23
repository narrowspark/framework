<?php
namespace Viserio\Cookie;

use DateTime;
use DateTimeInterface;
use Viserio\Contracts\Cookie\Cookie as CookieContract;
use Viserio\Contracts\Support\Stringable;

abstract class AbstractCookie implements Stringable, CookieContract
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $value;

    /**
     * @var string|null
     */
    protected $domain;

    /**
     * @var int|\DateTimeInterface
     */
    protected $expires;

    /**
     * @var int|null
     */
    protected $maxAge;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $secure;

    /**
     * @var bool
     */
    protected $httpOnly;

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the value
     *
     * @param string|null $value
     *
     * @return self
     */
    abstract public function withValue(string $value = null): CookieContract;

    /**
     * Returns the value
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Checks if there is a value
     *
     * @return bool
     */
    public function hasValue(): bool
    {
        return !empty($this->value);
    }

    /**
     * Sets the max age
     *
     * @param int|null $maxAge
     *
     * @return self
     */
    public function withMaxAge(int $maxAge = null): CookieContract
    {
        $new = clone $this;
        $new->maxAge = is_int($maxAge) ? $maxAge : null;

        return $new;
    }

    /**
     * Returns the max age
     *
     * @return int|null
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * Checks if there is a max age
     *
     * @return bool
     */
    public function hasMaxAge(): bool
    {
        return $this->maxAge !== null;
    }

    /**
     * Sets both the max age and the expires attributes
     *
     * @param int|null $expiration
     *
     * @return self
     */
    public function withExpiration(int $expiration = null): CookieContract
    {
        $new = clone $this;
        $new->maxAge = is_int($expiration) ? $expiration : null;
        $new->expires = $this->normalizeExpires($expiration);

        return $new;
    }

    /**
     * Sets the expires
     *
     * @param \DateTimeInterface $expires
     *
     * @return self
     */
    public function withExpires(DateTimeInterface $expires): CookieContract
    {
        $new = clone $this;
        $new->expires = $expires;

        return $new;
    }

    /**
     * Returns the expiration time
     *
     * @return DateTimeInterface
     */
    public function getExpiresTime(): DateTimeInterface
    {
        return $this->expires;
    }

    /**
     * Checks if there is an expiration time
     *
     * @return bool
     */
    public function hasExpires(): bool
    {
        return $this->expires !== 0;
    }

    /**
     * Checks if the cookie is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires !== 0 && $this->expires < new DateTime();
    }

    /**
     * Sets the domain
     *
     * @param string|null $domain
     *
     * @return self
     */
    public function withDomain(string $domain = null): CookieContract
    {
        $new = clone $this;
        $new->domain = $this->normalizeDomain($domain);

        return $new;
    }

    /**
     * Returns the domain
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Checks if there is a domain
     *
     * @return bool
     */
    public function hasDomain(): bool
    {
        return $this->domain !== null;
    }

    /**
     * Sets the path
     *
     * @param string|null $path
     *
     * @return self
     */
    public function withPath(string $path = null): CookieContract
    {
        $new = clone $this;
        $new->path = $this->normalizePath($path);

        return $new;
    }

    /**
     * Returns the path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Sets the secure
     *
     * @param bool $secure
     *
     * @return self
     */
    public function withSecure(bool $secure): CookieContract
    {
        $new = clone $this;
        $new->secure = filter_var($secure, FILTER_VALIDATE_BOOLEAN);

        return $new;
    }

    /**
     * Checks if HTTPS is required
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * Sets the HTTP Only
     *
     * @param bool $httpOnly
     *
     * @return self
     */
    public function withHttpOnly(bool $httpOnly): CookieContract
    {
        $new = clone $this;
        $new->httpOnly = filter_var($httpOnly, FILTER_VALIDATE_BOOLEAN);

        return $new;
    }

    /**
     * Checks if it is HTTP-only
     *
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * It matches a path
     *
     * @param string $path
     *
     * @return bool
     *
     * @link http://tools.ietf.org/html/rfc6265#section-5.1.4
     */
    public function matchPath(string $path): bool
    {
        return $this->path === $path || (strpos($path, $this->path . '/') === 0);
    }

    /**
     * Checks if it matches with another cookie
     *
     * @param \Viserio\Contracts\Cookie\Cookie $cookie
     *
     * @return bool
     */
    public function matchCookie(CookieContract $cookie): bool
    {
        return $this->getName() === $cookie->getName() &&
            $this->getDomain() === $cookie->getDomain() &&
            $this->getPath() === $cookie->getPath();
    }

    /**
     * Matches a domain
     *
     * @param string $domain
     *
     * @return bool
     *
     * @link http://tools.ietf.org/html/rfc6265#section-5.1.3
     */
    public function matchDomain(string $domain): bool
    {
        // Domain is not set or exact match
        if (!$this->hasDomain() || strcasecmp($domain, $this->getDomain()) === 0) {
            return true;
        }

        // Domain is not an IP address
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) preg_match('/\b' . preg_quote($this->getDomain()) . '$/i', $domain);
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    abstract public function __toString(): string;

    /**
     * Normalizes the expiration value
     *
     * @param int|null $expiration
     *
     * @return \DateTime|null
     */
    protected function normalizeExpires(int $expiration = null)
    {
        $expires = null;

        if (is_int($expiration)) {
            $expires = new DateTime(sprintf('%d seconds', $expiration));

            // According to RFC 2616 date should be set to earliest representable date
            if ($expiration <= 0) {
                $expires->setTimestamp(-PHP_INT_MAX);
            }
        } elseif ($expiration instanceof DateTime) {
            $expires = $expiration;
        }

        return $expires;
    }

    /**
     * Remove the leading '.' and lowercase the domain as per spec in RFC 6265
     *
     * @param string|null $domain
     *
     * @return string|null
     *
     * @link http://tools.ietf.org/html/rfc6265#section-4.1.2.3
     * @link http://tools.ietf.org/html/rfc6265#section-5.1.3
     * @link http://tools.ietf.org/html/rfc6265#section-5.2.3
     */
    protected function normalizeDomain(string $domain = null)
    {
        if (isset($domain)) {
            $domain = ltrim(strtolower($domain), '.');
        }

        return $domain;
    }

    /**
     * Processes path as per spec in RFC 6265
     *
     * @param string|null $path
     *
     * @return string
     *
     * @link http://tools.ietf.org/html/rfc6265#section-5.1.4
     * @link http://tools.ietf.org/html/rfc6265#section-5.2.4
     */
    protected function normalizePath(string $path = null): string
    {
        $path = rtrim($path, '/');

        if (empty($path) || substr($path, 0, 1) !== '/') {
            $path = '/';
        }

        return $path;
    }

    protected function appendFormattedNameAndValuePartIfSet(array $cookieStringParts)
    {
        $name = urlencode($this->name) . '=';

        if ((string) $this->getValue() === '') {
            $cookieStringParts[] .= $name . 'deleted; Expires=' . gmdate('D, d-M-Y H:i:s T', time() - 31536001);
        } else {
            $cookieStringParts[] .= $name . urlencode($this->getValue());

            if ($this->getExpiresTime()->format('s') !== 0) {
                $cookieStringParts[] .= 'Expires=' . $this->getExpiresTime()->format('D, d-M-Y H:i:s T');
            }
        }

        return $cookieStringParts;
    }

    protected function appendFormattedDomainPartIfSet(array $cookieStringParts)
    {
        if ($this->domain) {
            $cookieStringParts[] = sprintf('Domain=%s', $this->domain);
        }

        return $cookieStringParts;
    }

    protected function appendFormattedPathPartIfSet(array $cookieStringParts)
    {
        if ($this->path) {
            $cookieStringParts[] = sprintf('Path=%s', $this->path);
        }

        return $cookieStringParts;
    }

    protected function appendFormattedMaxAgePartIfSet(array $cookieStringParts)
    {
        if ($this->maxAge) {
            $cookieStringParts[] = sprintf('Max-Age=%s', $this->maxAge);
        }

        return $cookieStringParts;
    }

    protected function appendFormattedSecurePartIfSet(array $cookieStringParts)
    {
        if ($this->secure) {
            $cookieStringParts[] = 'Secure';
        }

        return $cookieStringParts;
    }

    protected function appendFormattedHttpOnlyPartIfSet(array $cookieStringParts)
    {
        if ($this->httpOnly) {
            $cookieStringParts[] = 'HttpOnly';
        }

        return $cookieStringParts;
    }
}
