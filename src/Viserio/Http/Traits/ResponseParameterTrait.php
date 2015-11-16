<?php
namespace Viserio\Http\Traits;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Symfony\Component\HttpFoundation\Cookie;

/**
 * ResponseParameterTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
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
