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
     */
    public function unqueue(string $name): void;

    /**
     * Determine if a cookie has been queued.
     */
    public function hasQueued(string $key): bool;

    /**
     * Get a queued cookie instance.
     *
     * @param null|mixed $default
     *
     * @return null|\Viserio\Contract\Cookie\Cookie
     */
    public function queued(string $key, $default = null): ?Cookie;

    /**
     * Get the cookies which have been queued for the next request.
     */
    public function getQueuedCookies(): array;
}
