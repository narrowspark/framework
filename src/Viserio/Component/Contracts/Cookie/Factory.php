<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Cookie;

interface Factory
{
    /**
     * Create a new cookie instance.
     *
     * @param string           $name
     * @param string|null      $value
     * @param int              $minutes
     * @param string|null      $path
     * @param string|null      $domain
     * @param bool             $secure
     * @param bool             $httpOnly
     * @param string|bool|null $sameSite
     *
     * @return \Viserio\Component\Contracts\Cookie\Cookie
     */
    public function create(
        string $name,
        ?string $value = null,
        int $minutes = 0,
        ?string $path = null,
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        $sameSite = false
    ): Cookie;

    /**
     * Create a cookie that lasts "forever" (five years).
     *
     * @param string           $name
     * @param string|null      $value
     * @param string|null      $path
     * @param string|null      $domain
     * @param bool             $secure
     * @param bool             $httpOnly
     * @param string|bool|null $sameSite
     *
     * @return \Viserio\Component\Contracts\Cookie\Cookie
     */
    public function forever(
        string $name,
        ?string $value = null,
        ?string $path = null,
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        $sameSite = false
    ): Cookie;

    /**
     * Expire the given cookie.
     *
     * @param string      $name
     * @param string|null $path
     * @param string|null $domain
     *
     * @return \Viserio\Component\Contracts\Cookie\Cookie
     */
    public function delete(
        string $name,
        ?string $path = null,
        ?string $domain = null
    ): Cookie;
}
