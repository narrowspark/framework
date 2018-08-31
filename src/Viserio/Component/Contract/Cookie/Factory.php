<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Cookie;

interface Factory
{
    /**
     * Create a new cookie instance.
     *
     * @param string           $name
     * @param null|string      $value
     * @param int              $second
     * @param null|string      $path
     * @param null|string      $domain
     * @param bool             $secure
     * @param bool             $httpOnly
     * @param null|bool|string $sameSite
     *
     * @return \Viserio\Component\Contract\Cookie\Cookie
     */
    public function create(
        string $name,
        ?string $value  = null,
        int $second     = 0,
        ?string $path   = null,
        ?string $domain = null,
        bool $secure    = false,
        bool $httpOnly  = true,
        $sameSite       = false
    ): Cookie;

    /**
     * Create a cookie that lasts "forever" (five years).
     *
     * @param string           $name
     * @param null|string      $value
     * @param null|string      $path
     * @param null|string      $domain
     * @param bool             $secure
     * @param bool             $httpOnly
     * @param null|bool|string $sameSite
     *
     * @return \Viserio\Component\Contract\Cookie\Cookie
     */
    public function forever(
        string $name,
        ?string $value  = null,
        ?string $path   = null,
        ?string $domain = null,
        bool $secure    = false,
        bool $httpOnly  = true,
        $sameSite       = false
    ): Cookie;

    /**
     * Expire the given cookie.
     *
     * @param string      $name
     * @param null|string $path
     * @param null|string $domain
     *
     * @return \Viserio\Component\Contract\Cookie\Cookie
     */
    public function delete(string $name, ?string $path = null, ?string $domain = null): Cookie;
}
