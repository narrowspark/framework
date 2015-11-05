<?php
namespace Brainwave\Contracts\Http;

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

/**
 * Request.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Request
{
    /**
     * Return array or single key from $_GET.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function query($key = null, $default = null);

    /**
     * Return array or single key from $_POST.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function post($key = null, $default = null);

    /**
     * Return array or single key from $_SERVER.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function server($key = null, $default = null);

    /**
     * Return array or single key from $_FILES.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function files($key = null, $default = null);

    /**
     * Return array or single key from $_COOKIE.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function getCookie($key = null, $default = null);

    /**
     * Return array or single key from headers taken from $_SERVER.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return string
     */
    public function headers($key = null, $default = null);

    /**
     * Get a segment from the URI string.
     *
     * @param int         $index
     * @param string|null $default
     *
     * @return string
     */
    public function uriSegment($index, $default = null);

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function ajax();

    /**
     * Determine if the current request is asking for JSON in return.
     *
     * @return bool
     */
    public function wantsJson();

    /**
     * Determine if a cookie is set on the request.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasCookie($key);
}
