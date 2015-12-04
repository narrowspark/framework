<?php
namespace Viserio\Http\Traits;

use Symfony\Component\HttpFoundation\Cookie;

trait ResponseParameterTrait
{
    /**
     * Return array or single key from $_COOKIE.
     *
     * @param string|null $key
     * @param null        $default
     *
     * @return mixed
     */
    public function cookie($key = null, $default = null)
    {
        if (null === $key) {
            return $this->headers->getCookies();
        }

        return (isset($this->headers->getCookies()[$key])) ? $this->headers->getCookies()[$key] : $default;
    }

    /**
     * Add a cookie to the response.
     *
     * @param \Symfony\Component\HttpFoundation\Cookie $cookie
     *
     * @return $this
     */
    public function withCookie(Cookie $cookie)
    {
        $this->headers->setCookie($cookie);

        return $this;
    }

    /**
     * Return array or single key from headers taken from $_SERVER.
     *
     * @param string|null $key
     * @param string      $default
     *
     * @return mixed
     */
    public function headers($key = null, $default = null)
    {
        if (null === $key) {
            return $this->headers;
        }

        return $this->headers->get($key, $default);
    }
}
