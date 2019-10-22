<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Cookie;

interface QueueingFactory extends Factory
{
    /**
     * Queue a cookie to send with the next response.
     *
     * @param array<int, mixed> $arguments
     */
    public function queue(...$arguments);

    /**
     * Remove a cookie from the queue.
     *
     * @param string $name
     *
     * @return void
     */
    public function unqueue(string $name): void;

    /**
     * Determine if a cookie has been queued.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasQueued(string $key): bool;

    /**
     * Get a queued cookie instance.
     *
     * @param string     $key
     * @param null|mixed $default
     *
     * @return null|\Viserio\Contract\Cookie\Cookie
     */
    public function queued(string $key, $default = null): ?Cookie;

    /**
     * Get the cookies which have been queued for the next request.
     *
     * @return array
     */
    public function getQueuedCookies(): array;
}
