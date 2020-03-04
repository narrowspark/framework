<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Cookie;

interface Factory
{
    /**
     * Create a new cookie instance.
     *
     * @param null|bool|string $sameSite
     *
     * @return \Viserio\Contract\Cookie\Cookie
     */
    public function create(
        string $name,
        ?string $value = null,
        int $second = 0,
        ?string $path = null,
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        $sameSite = false
    ): Cookie;

    /**
     * Create a cookie that lasts "forever" (five years).
     *
     * @param null|bool|string $sameSite
     *
     * @return \Viserio\Contract\Cookie\Cookie
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
     * @return \Viserio\Contract\Cookie\Cookie
     */
    public function delete(string $name, ?string $path = null, ?string $domain = null): Cookie;
}
