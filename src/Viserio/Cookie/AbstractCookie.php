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
     * @var string
     */
    protected $sameSite;

    /**
     * {@inheritdoc}
     */
    abstract public function __toString(): string;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function withValue(string $value = null): CookieContract;

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue(): bool
    {
        return ! empty($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function withMaxAge(int $maxAge = null): CookieContract
    {
        $new = clone $this;
        $new->maxAge = $maxAge;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMaxAge(): bool
    {
        return $this->maxAge !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function withExpiration($expiration = null): CookieContract
    {
        $new = clone $this;
        $new->maxAge = is_int($expiration) ? $expiration : null;
        $new->expires = $this->normalizeExpires($expiration);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withExpires($expires): CookieContract
    {
        $new = clone $this;
        $new->expires = $this->normalizeExpires($expires);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresTime()
    {
        return $this->expires;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExpires(): bool
    {
        return $this->expires !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isExpired(): bool
    {
        return $this->expires !== 0 && $this->expires < new DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function withDomain(string $domain = null): CookieContract
    {
        $new = clone $this;
        $new->domain = $this->normalizeDomain($domain);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDomain(): bool
    {
        return $this->domain !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath(string $path = '/'): CookieContract
    {
        $new = clone $this;
        $new->path = $this->normalizePath($path);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function withSecure(bool $secure): CookieContract
    {
        $new = clone $this;
        $new->secure = filter_var($secure, FILTER_VALIDATE_BOOLEAN);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * {@inheritdoc}
     */
    public function withHttpOnly(bool $httpOnly): CookieContract
    {
        $new = clone $this;
        $new->httpOnly = filter_var($httpOnly, FILTER_VALIDATE_BOOLEAN);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function withSameSite(string $sameSite): CookieContract
    {
        $new = clone $this;
        $new->sameSite = $this->validateSameSite($sameSite);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function isSameSite(): bool
    {
        return $this->sameSite !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSameSite()
    {
        return $this->sameSite;
    }

    /**
     * {@inheritdoc}
     */
    public function matchPath(string $path): bool
    {
        return $this->path === $path || (strpos($path, $this->path . '/') === 0);
    }

    /**
     * {@inheritdoc}
     */
    public function matchCookie(CookieContract $cookie): bool
    {
        return $this->getName() === $cookie->getName() &&
            $this->getDomain() === $cookie->getDomain() &&
            $this->getPath() === $cookie->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function matchDomain(string $domain): bool
    {
        // Domain is not set or exact match
        if (! $this->hasDomain() || strcasecmp($domain, $this->getDomain()) === 0) {
            return true;
        }

        // Domain is not an IP address
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) preg_match('/\b' . preg_quote($this->getDomain()) . '$/i', $domain);
    }

    /**
    * Validate SameSite value.
     *
     * @param string|bool $sameSite
     *
     * @return string|bool
     */
    protected function validateSameSite($sameSite)
    {
        if (!in_array($sameSite, [self::SAMESITE_STRICT, self::SAMESITE_LAX])) {
            return false;
        }

        return $sameSite;
    }

    /**
     * Normalizes the expiration value
     *
     * @param int|string|\DateTimeInterface|null $expiration
     *
     * @return \DateTimeInterface|null
     */
    protected function normalizeExpires($expiration = null)
    {
        $expires = null;

        if (is_int($expiration)) {
            $expires = new DateTime(sprintf('%d seconds', $expiration));

            // According to RFC 2616 date should be set to earliest representable date
            if ($expiration <= 0) {
                $expires->setTimestamp(-PHP_INT_MAX);
            }
        } elseif ($expiration instanceof DateTimeInterface) {
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
     * @param string $path
     *
     * @return string
     *
     * @link http://tools.ietf.org/html/rfc6265#section-5.1.4
     * @link http://tools.ietf.org/html/rfc6265#section-5.2.4
     */
    protected function normalizePath(string $path): string
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

    protected function appendFormattedSameSitePartIfSet(array $cookieStringParts)
    {
        if ($this->sameSite) {
            $cookieStringParts[] = sprintf('SameSite=%s', $this->sameSite);
        }

        return $cookieStringParts;
    }
}
