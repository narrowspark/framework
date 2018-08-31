<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie;

use Cake\Chronos\Chronos;
use DateTime;
use DateTimeInterface;
use Viserio\Component\Contract\Cookie\Cookie as CookieContract;
use Viserio\Component\Contract\Cookie\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Support\Stringable as StringableContract;

abstract class AbstractCookie implements StringableContract, CookieContract
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var null|string
     */
    protected $value;

    /**
     * @var null|string
     */
    protected $domain;

    /**
     * @var null|int
     */
    protected $expires;

    /**
     * @var null|int
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
     * @var null|bool|string
     */
    protected $sameSite;

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
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxAge(): ?int
    {
        return $this->maxAge;
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
    public function isSecure(): bool
    {
        return $this->secure;
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
    public function getSameSite()
    {
        return $this->sameSite;
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
    abstract public function withValue(string $value = null): CookieContract;

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
        $new         = clone $this;
        $new->maxAge = $maxAge;

        return $new;
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
    public function withExpires($expires): CookieContract
    {
        $new          = clone $this;
        $new->expires = $this->normalizeExpires($expires);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresTime(): ?int
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
        return $this->expires !== 0 &&
            Chronos::parse(\gmdate('D, d-M-Y H:i:s', $this->expires)) < Chronos::now();
    }

    /**
     * {@inheritdoc}
     */
    public function withDomain(string $domain = null): CookieContract
    {
        $new         = clone $this;
        $new->domain = $this->normalizeDomain($domain);

        return $new;
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
        $new       = clone $this;
        $new->path = $this->normalizePath($path);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withSecure(bool $secure): CookieContract
    {
        $new         = clone $this;
        $new->secure = $secure;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withHttpOnly(bool $httpOnly): CookieContract
    {
        $new           = clone $this;
        $new->httpOnly = $httpOnly;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withSameSite($sameSite): CookieContract
    {
        $new           = clone $this;
        $new->sameSite = $this->validateSameSite($sameSite);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function matchPath(string $path): bool
    {
        return $this->path === $path || (\mb_strpos($path, $this->path . '/') === 0);
    }

    /**
     * {@inheritdoc}
     */
    public function matchCookie(CookieContract $cookie): bool
    {
        return $this->getName() === $cookie->getName()   &&
            $this->getDomain()  === $cookie->getDomain() &&
            $this->getPath()    === $cookie->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function matchDomain(string $domain): bool
    {
        // Domain is not set or exact match
        if (! $this->hasDomain() || \strcasecmp($domain, $this->getDomain()) === 0) {
            return true;
        }

        // Domain is not an IP address
        if (\filter_var($domain, \FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) \preg_match('/\b' . \preg_quote($this->getDomain(), '/') . '$/i', $domain);
    }

    /**
     * Validate SameSite value.
     *
     * @param bool|string $sameSite
     *
     * @return bool|string
     */
    protected function validateSameSite($sameSite)
    {
        if (! \in_array($sameSite, [self::SAMESITE_STRICT, self::SAMESITE_LAX], true)) {
            return false;
        }

        return $sameSite;
    }

    /**
     * Normalizes the expiration value.
     *
     * @param null|\DateTimeInterface|int|string $expiration
     *
     * @throws \Viserio\Component\Contract\Cookie\Exception\InvalidArgumentException
     *
     * @return int
     */
    protected function normalizeExpires($expiration = null): int
    {
        $expires   = $this->getTimestamp($expiration);
        $tsExpires = $expires;

        if (\is_string($expires)) {
            $tsExpires = \strtotime($expires);
            $is32Bit   = \PHP_INT_SIZE <= 4;

            // if $tsExpires is invalid and PHP is compiled as 32bit. Check if it fail reason is the 2038 bug
            if ($is32Bit && ! \is_int($tsExpires)) {
                $dateTime = new Chronos($expires);

                if ($dateTime->format('Y') > 2038) {
                    $tsExpires = \PHP_INT_MAX;
                }
            }
        }

        if (! \is_int($tsExpires) || $tsExpires < 0) {
            throw new InvalidArgumentException('Invalid expires time specified.');
        }

        return $tsExpires;
    }

    /**
     * Remove the leading '.' and lowercase the domain as per spec in RFC 6265.
     *
     * @param null|string $domain
     *
     * @return null|string
     *
     * @see http://tools.ietf.org/html/rfc6265#section-4.1.2.3
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.3
     * @see http://tools.ietf.org/html/rfc6265#section-5.2.3
     */
    protected function normalizeDomain(string $domain = null): ?string
    {
        if ($domain !== null) {
            $domain = \mb_strtolower(\ltrim($domain, '.'));
        }

        return $domain;
    }

    /**
     * Processes path as per spec in RFC 6265.
     *
     * @param string $path
     *
     * @return string
     *
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.4
     * @see http://tools.ietf.org/html/rfc6265#section-5.2.4
     */
    protected function normalizePath(string $path): string
    {
        $path = \rtrim($path, '/');

        if (empty($path) || \mb_strpos($path, '/')) {
            $path = '/';
        }

        return $path;
    }

    /**
     * @param array $cookieStringParts
     *
     * @return array
     */
    protected function appendFormattedNameAndValuePartIfSet(array $cookieStringParts): array
    {
        $name = \urlencode($this->name) . '=';

        if ($this->getValue() === null) {
            $time = Chronos::now()->getTimestamp() - 31536001;

            $cookieStringParts[] .= $name . 'deleted; Expires=' . (new Chronos(\gmdate('D, d-M-Y H:i:s', $time)))->toCookieString();
        } else {
            $cookieStringParts[] .= $name . \urlencode($this->getValue());

            if (null !== $this->getExpiresTime()) {
                $cookieStringParts[] .= 'Expires=' . (new Chronos(\gmdate('D, d-M-Y H:i:s', $this->getExpiresTime())))->toCookieString();
            }
        }

        return $cookieStringParts;
    }

    /**
     * @param array $cookieStringParts
     *
     * @return array
     */
    protected function appendFormattedDomainPartIfSet(array $cookieStringParts): array
    {
        if ($this->domain !== null) {
            $cookieStringParts[] = \sprintf('Domain=%s', $this->domain);
        }

        return $cookieStringParts;
    }

    /**
     * @param array $cookieStringParts
     *
     * @return array
     */
    protected function appendFormattedPathPartIfSet(array $cookieStringParts): array
    {
        if ($this->path !== null) {
            $cookieStringParts[] = \sprintf('Path=%s', $this->path);
        }

        return $cookieStringParts;
    }

    /**
     * @param array $cookieStringParts
     *
     * @return array
     */
    protected function appendFormattedMaxAgePartIfSet(array $cookieStringParts): array
    {
        if ($this->maxAge) {
            $cookieStringParts[] = \sprintf('Max-Age=%s', $this->maxAge);
        }

        return $cookieStringParts;
    }

    /**
     * @param array $cookieStringParts
     *
     * @return array
     */
    protected function appendFormattedSecurePartIfSet(array $cookieStringParts): array
    {
        if ($this->secure) {
            $cookieStringParts[] = 'Secure';
        }

        return $cookieStringParts;
    }

    /**
     * @param array $cookieStringParts
     *
     * @return array
     */
    protected function appendFormattedHttpOnlyPartIfSet(array $cookieStringParts): array
    {
        if ($this->httpOnly) {
            $cookieStringParts[] = 'HttpOnly';
        }

        return $cookieStringParts;
    }

    /**
     * @param array $cookieStringParts
     *
     * @return array
     */
    protected function appendFormattedSameSitePartIfSet(array $cookieStringParts): array
    {
        if ($this->sameSite) {
            $cookieStringParts[] = \sprintf('SameSite=%s', $this->sameSite);
        }

        return $cookieStringParts;
    }

    /**
     * Check if the string / int is a unix timestamp.
     *
     * @param int|string $timestamp
     *
     * @return bool
     */
    protected function isValidTimeStamp($timestamp): bool
    {
        return ((int) $timestamp <= \PHP_INT_MAX) && ((int) $timestamp >= ~\PHP_INT_MAX);
    }

    /**
     * Get timestamp as cookie string format.
     *
     * @param null|\DateTimeInterface|int|string $expiration
     *
     * @return null|string
     */
    protected function getTimestamp($expiration): ?string
    {
        if (\is_int($expiration) && \mb_strlen((string) $expiration) === 10 && $this->isValidTimeStamp($expiration)) {
            return Chronos::createFromTimestamp($expiration)->toCookieString();
        }

        if (\is_int($expiration)) {
            return Chronos::now()->addSeconds($expiration)->toCookieString();
        }

        if ($expiration instanceof DateTimeInterface) {
            return $expiration->format(DateTime::COOKIE);
        }

        if (\is_string($expiration)) {
            return $expiration;
        }

        return null;
    }
}
