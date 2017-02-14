<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Cookie;

interface QueueingFactory extends Factory
{
    /**
     * Queue a cookie to send with the next response.
     *
     * @param array $arguments
     */
    public function queue(...$arguments);

    /**
     * Remove a cookie from the queue.
     *
     * @param string $name
     *
     * @return void
     */
    public function unqueue(string $name);

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
     * @param mixed|null $default
     *
     * @return CookieContract|null
     */
    public function queued(string $key, $default = null);

    /**
     * Get the cookies which have been queued for the next request.
     *
     * @return array
     */
    public function getQueuedCookies(): array;
}
