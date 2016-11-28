<?php
declare(strict_types=1);
namespace Viserio\Cookie;

use Cake\Chronos\Chronos;
use Narrowspark\Arr\Arr;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\Cookie\Cookie as CookieContract;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;

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
     * @param string|null $value
     * @param int         $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool        $secure
     * @param bool        $httpOnly
     * @param string|bool $sameSite
     *
     * @return Cookie
     */
    public function create(
        string $name,
        $value,
        int $minutes = 0,
        $path = null,
        $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        $sameSite = false
    ): CookieContract {
        list($path, $domain, $secure) = $this->getPathAndDomain($path, $domain, $secure);

        $time = ($minutes === 0) ? 0 : Chronos::now()->getTimestamp() + ($minutes * 60);

        return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $sameSite);
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
     * @param string|bool $sameSite
     *
     * @return Cookie
     */
    public function forever(
        string $name,
        string $value,
        $path = null,
        $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        $sameSite = false
    ): CookieContract {
        return $this->create($name, $value, 2628000, $path, $domain, $secure, $httpOnly, $sameSite);
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
    public function delete(string $name, $path = null, $domain = null): CookieContract
    {
        return $this->create($name, null, -2628000, $path, $domain);
    }

    /**
     * Determine if a cookie has been queued.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasQueued(string $key): bool
    {
        return $this->queued($key) !== null;
    }

    /**
     * Get a queued cookie instance.
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return CookieContract|null
     */
    public function queued(string $key, $default = null)
    {
        return Arr::get($this->queued, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function queue(...$arguments)
    {
        if (reset($arguments) instanceof CookieContract) {
            $cookie = reset($arguments);
        } else {
            $cookie = call_user_func_array([$this, 'create'], $arguments);
        }

        $this->queued[$cookie->getName()] = $cookie;
    }

    /**
     * Remove a cookie from the queue.
     *
     * @param string $name
     */
    public function unqueue(string $name)
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
    public function setDefaultPathAndDomain(string $path, string $domain, bool $secure = false): JarContract
    {
        list($this->path, $this->domain, $this->secure) = [$path, $domain, $secure];

        return $this;
    }

    /**
     * Get the cookies which have been queued for the next request.
     *
     * @return array
     */
    public function getQueuedCookies(): array
    {
        return $this->queued;
    }

    /**
     * Get the path and domain, or the default values.
     *
     * @param string|null $path
     * @param string|null $domain
     * @param bool        $secure
     *
     * @return string[]
     */
    protected function getPathAndDomain($path, $domain, bool $secure = false): array
    {
        return [$path ?? $this->path, $domain ?? $this->domain, $secure ?? $this->secure];
    }
}
