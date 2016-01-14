<?php
namespace Viserio\Cookie;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;
use Narrowspark\Arr\StaticArr as Arr;

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
     * Render SetCookies into a Response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function renderIntoSetCookieHeader(ResponseInterface $response)
    {
        $response = $response->withoutHeader('Set-Cookie');

        foreach ($this->queued as $cookie) {
            $response = $response->withAddedHeader('Set-Cookie', $cookie->__toString());
        }

        return $response;
    }

    /**
     * Render Cookies into a Request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function renderIntoCookieHeader(RequestInterface $request)
    {
        $cookieString = implode('; ', $this->queued);

        $request = $request->withHeader('Cookie', $cookieString);

        return $request;
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
     * @param bool   $secure
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
}
