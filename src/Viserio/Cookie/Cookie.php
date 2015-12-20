<?php
namespace Viserio\Cookie;

use DateTime;
use InvalidArgumentException;
use Viserio\Contracts\Support\Stringable;

class Cookie implements Stringable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var int|\DateTime
     */
    protected $expire;

    /**
     * @var integer|null
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
     * @param string            $name       The name of the cookie
     * @param string|null       $value      The value of the cookie
     * @param integer|\DateTime $expiration The time the cookie expires
     * @param string|null       $domain     The domain that the cookie is available to
     * @param string|null       $path       The path on the server in which the cookie will be available on
     * @param boolean           $secure     Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     * @param boolean           $httpOnly   Whether the cookie will be made accessible only through the HTTP protocol
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $name,
        $value = null,
        $expiration = 0,
        $domain = null,
        $path = null,
        $secure = false,
        $httpOnly = false
    ) {
        $this->validateName($name);
        $this->validateValue($value);

        $maxAge = $expires = null;

        if (is_int($expiration)) {
            $maxAge = $expiration;
            $expires = new DateTime(sprintf('%d seconds', $maxAge));

            // According to RFC 2616 date should be set to earliest representable date
            if ($maxAge <= 0) {
                $expires->setTimestamp(-PHP_INT_MAX);
            }
        } elseif ($expiration instanceof DateTime) {
            $expires = $expiration;
        }

        $this->name     = $name;
        $this->value    = $value;
        $this->maxAge   = $maxAge;
        $this->expires  = $expires;
        $this->domain   = $this->normalizeDomain($domain);
        $this->path     = $this->normalizePath($path);
        $this->secure   = (bool) $secure;
        $this->httpOnly = (bool) $httpOnly;
    }

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

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
     * @return boolean
     */
    public function hasValue()
    {
        return isset($this->value);
    }

    /**
     * Returns the max age
     *
     * @return integer|null
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * Checks if there is a max age
     *
     * @return boolean
     */
    public function hasMaxAge()
    {
        return isset($this->maxAge);
    }

    /**
     * Returns the expiration time
     *
     * @return \DateTime|null
     */
    public function getExpiresTime()
    {
        return $this->expires;
    }

    /**
     * Checks if there is an expiration time
     *
     * @return boolean
     */
    public function hasExpires()
    {
        return isset($this->expires);
    }

    /**
     * Checks if the cookie is expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        return isset($this->expires) && $this->expires < new DateTime();
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
     * @return boolean
     */
    public function hasDomain()
    {
        return isset($this->domain);
    }

    /**
     * Returns the path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    /**
     * It matches a path
     *
     * @param string $path
     *
     * @return boolean
     *
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.4
     */
    public function matchPath($path)
    {
        return $this->path === $path || (strpos($path, $this->path.'/') === 0);
    }
    /**
     * Checks if HTTPS is required
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Checks if it is HTTP-only
     *
     * @return boolean
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Checks if it matches with another cookie
     *
     * @param Cookie $cookie
     *
     * @return boolean
     */
    public function match(Cookie $cookie)
    {
        return $this->name === $cookie->name && $this->domain === $cookie->domain and $this->path === $cookie->path;
    }

    /**
     * Matches a domain
     *
     * @param string $domain
     *
     * @return boolean
     *
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.3
     */
    public function matchDomain($domain)
    {
        // Domain is not set or exact match
        if (!$this->hasDomain() || strcasecmp($domain, $this->domain) === 0) {
            return true;
        }

        // Domain is not an IP address
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) preg_match('/\b' . preg_quote($this->domain) . '$/i', $domain);
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString()
    {
        $str = urlencode($this->getName()).'=';

        if ((string) $this->getValue() === '') {
            $str .= 'deleted; expires='.gmdate('D, d-M-Y H:i:s T', time() - 31536001);
        } else {
            $str .= urlencode($this->getValue());
            if ($this->getExpiresTime() !== 0) {
                $str .= '; expires='.gmdate('D, d-M-Y H:i:s T', $this->getExpiresTime());
            }
        }

        if ($this->path) {
            $str .= '; path='.$this->path;
        }

        if ($this->getDomain()) {
            $str .= '; domain='.$this->getDomain();
        }

        if ($this->isSecure() === true) {
            $str .= '; secure';
        }

        if ($this->isHttpOnly() === true) {
            $str .= '; httponly';
        }

        return $str;
    }

    /**
     * Validates the name attribute
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @see http://tools.ietf.org/search/rfc2616#section-2.2
     */
    private function validateName($name)
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
     * @see http://tools.ietf.org/html/rfc6265#section-4.1.1
     */
    private function validateValue($value)
    {
        if (isset($value)) {
            if (preg_match('/[^\x21\x23-\x2B\x2D-\x3A\x3C-\x5B\x5D-\x7E]/', $value)) {
                throw new InvalidArgumentException(sprintf('The cookie value "%s" contains invalid characters.', $value));
            }
        }
    }

        /**
     * Remove the leading '.' and lowercase the domain as per spec in RFC 6265
     *
     * @param string|null $domain
     *
     * @return string
     *
     * @see http://tools.ietf.org/html/rfc6265#section-4.1.2.3
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.3
     * @see http://tools.ietf.org/html/rfc6265#section-5.2.3
     */
    private function normalizeDomain($domain)
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
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.4
     * @see http://tools.ietf.org/html/rfc6265#section-5.2.4
     */
    private function normalizePath($path)
    {
        $path = rtrim($path, '/');

        if (empty($path) or substr($path, 0, 1) !== '/') {
            $path = '/';
        }

        return $path;
    }
}
