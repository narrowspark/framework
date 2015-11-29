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
 * @version     0.10.0
 */

/**
 * RequestParameterTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
trait RequestParameterTrait
{
    /**
     * Return array or single key from $_GET.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function query($key = null, $default = null)
    {
        if ($key === null) {
            return $this->query->all();
        }

        return $this->query->get($key, $default);
    }

    /**
     * Return array or single key from $_POST.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->request->all();
        }

        return $this->request->get($key, $default);
    }

    /**
     * Return array or single key from $_SERVER.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function server($key = null, $default = null)
    {
        if ($key === null) {
            return $this->server->all();
        }

        return $this->server->get($key, $default);
    }

    /**
     * Determine if the server has a key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasServer($key)
    {
        return $this->server->has($key);
    }

    /**
     * Return array or single key from $_FILES.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function files($key = null, $default = null)
    {
        if ($key === null) {
            return $this->files->all();
        }

        return $this->files->get($key, $default);
    }

    /**
     * Determine if the uploaded data contains a file.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasFiles($key)
    {
        return $this->files->has($key);
    }

    /**
     * Return array or single key from $_COOKIE.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function getCookie($key = null, $default = null)
    {
        if ($key === null) {
            return $this->cookies->all();
        }

        return $this->cookies->get($key, $default);
    }

    /**
     * Determine if a cookie is set on the request.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasCookie($key)
    {
        return $this->cookies->has($key);
    }

    /**
     * Return array or single key from headers taken from $_SERVER.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return string
     */
    public function headers($key = null, $default = null)
    {
        if ($key === null) {
            return $this->headers->all();
        }

        return $this->headers->get($key, $default);
    }

    /**
     * Determine if the header has a key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasHeaders($key)
    {
        return $this->headers->has($key);
    }
}
