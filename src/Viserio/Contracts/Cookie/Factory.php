<?php
namespace Viserio\Contracts\Cookie;

interface Factory
{
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
     * @return \Viserio\Cookie\Cookie
     */
    public function create(
        $name,
        $value,
        $minutes = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = true
    );

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
     * @return \Viserio\Cookie\Cookie
     */
    public function forever(
        $name,
        $value,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = true
    );

    /**
     * Expire the given cookie.
     *
     * @param string      $name
     * @param string|null $path
     * @param string|null $domain
     *
     * @return \Viserio\Cookie\Cookie
     */
    public function forget(
        $name,
        $path = null,
        $domain = null
    );
}
