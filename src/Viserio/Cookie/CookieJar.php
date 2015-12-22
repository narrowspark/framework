<?php
namespace Viserio\Cookie;

use DateTime;
use Psr\Http\Message\ServerRequestInterface as RequestContract;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Support\Arr;

class CookieJar implements JarContract
{
    /**
     * The default path (if specified).
     *
     * @var string
     */
    protected $path = '/';

    /**
     * The default domain (if specified).
     *
     * @var string
     */
    protected $domain = null;

    /**
     * The default secure setting.
     *
     * @var bool
     */
    protected $secure = false;

    /**
     * All of the cookies queued for sending.
     *
     * @var array
     */
    protected $queued = [];

    /**
     * Create a new cookie instance.
     *
     * @param string      $name
     * @param string      $value
     * @param int         $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool        $secure
     * @param bool        $httpOnly
     *
     * @return Cookie
     */
    public function create($name, $value, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        list($path, $domain, $secure) = $this->getPathAndDomain($path, $domain, $secure);

        $time = ($minutes === 0) ? 0 : time() + ($minutes * 60);

        return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Create a cookie that lasts "forever" (five years).
     *
     * @param string      $name
     * @param string      $value
     * @param string|null $path
     * @param string|null $domain
     * @param bool        $secure
     * @param bool        $httpOnly
     *
     * @return Cookie
     */
    public function forever($name, $value, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        return $this->create($name, $value, 2628000, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Expire the given cookie.
     *
     * @param string      $name
     * @param string|null $path
     * @param string|null $domain
     *
     * @return Cookie
     */
    public function forget($name, $path = null, $domain = null)
    {
        return $this->create($name, null, -2628000, $path, $domain);
    }

    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Viserio\Contracts\Cookie\Cookie
     */
    public function fromServerRequest(RequestContract $request)
    {
        return $this->fromStringCookie($request->getHeader('Set-Cookie'));
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toResponse(ResponseInterface $response)
    {
        foreach ($this->queued as $cookie) {
            $response = $response->withAddedHeader('Set-Cookie', $cookie->__toString());
        }

        return $response;
    }

    /**
     * Determine if a cookie has been queued.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasQueued($key)
    {
        return $this->queued($key) !== null;
    }

    /**
     * Get a queued cookie instance.
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return Cookie
     */
    public function queued($key, $default = null)
    {
        return Arr::get($this->queued, $key, $default);
    }

    /**
     * Queue a cookie to send with the next response.
     *
     * @param  mixed
     */
    public function queue()
    {
        $args = func_get_args();

        if (reset($args) instanceof Cookie) {
            $cookie = reset($args);
        } else {
            $cookie = call_user_func_array([$this, 'create'], $args);
        }

        $this->queued[$cookie->getName()] = $cookie;
    }

    /**
     * Remove a cookie from the queue.
     *
     * @param string $name
     */
    public function unqueue($name)
    {
        unset($this->queued[$name]);
    }

    /**
     * Set the default path and domain for the jar.
     *
     * @param string $path
     * @param string $domain
     *
     * @return $this
     */
    public function setDefaultPathAndDomain($path, $domain, $secure = false)
    {
        list($this->path, $this->domain, $this->secure) = [$path, $domain, $secure];

        return $this;
    }

    /**
     * Get the cookies which have been queued for the next request.
     *
     * @return array
     */
    public function getQueuedCookies()
    {
        return $this->queued;
    }

    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     *
     * @param string[] $string
     * @return \Viserio\Contracts\Cookie\Cookie
     */
    protected function fromStringCookie($string)
    {
        $rawAttributes = $this->splitOnAttributeDelimiter($string);

        list ($cookieName, $cookieValue) = $this->splitCookiePair($rawAttributes[0]);

        /** @var Cookie $cookie */
        $cookie = new Cookie($cookieName);

        if (!is_null($cookieValue)) {
            $cookie = $cookie->withValue($cookieValue);
        }

        foreach ($rawAttributes as $value) {
            $rawAttributePair = explode('=', $value, 2);
            $attributeKey     = $rawAttributePair[0];
            $attributeValue   = count($rawAttributePair) > 1 ? $rawAttributePair[1] : null;
            $attributeKey     = strtolower($attributeKey);

            switch ($attributeKey) {
                case 'expires':
                    $cookie = $cookie->withExpires(new DateTime($attributeValue));
                    break;
                case 'max-age':
                    $age = is_numeric($attributeValue) ? (int) $attributeValue : null;
                    $cookie = $cookie->withMaxAge($age);
                    break;
                case 'domain':
                    $cookie = $cookie->withDomain($attributeValue);
                    break;
                case 'path':
                    $cookie = $cookie->withPath($attributeValue);
                    break;
                case 'secure':
                    $cookie = $cookie->withSecure(true);
                    break;
                case 'httponly':
                    $cookie = $cookie->withHttpOnly(true);
                    break;
            }
        }

        return $cookie;
    }

    /**
     * Get the path and domain, or the default values.
     *
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     *
     * @return string[]
     */
    protected function getPathAndDomain($path, $domain, $secure = false)
    {
        return [$path ?: $this->path, $domain ?: $this->domain, $secure ?: $this->secure];
    }

    /**
     * Split a string to array.
     *
     * @param string $string
     *
     * @return array
     */
    protected function splitCookiePair($string)
    {
        $pairParts = explode('=', $string, 2);

        if (count($pairParts) === 1) {
            $pairParts[1] = '';
        }


        return array_map(function ($part) {
            return urldecode($part);
        }, $pairParts);
    }

    /**
     * spplit string on attributes delimiter to array.
     *
     * @param string $string
     *
     * @return array
     */
    protected function splitOnAttributeDelimiter($string)
    {
        return array_filter(preg_split('@\s*[;]\s*@', $string));
    }
}
