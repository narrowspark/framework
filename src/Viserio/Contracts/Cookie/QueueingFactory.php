<?php
namespace Viserio\Contracts\Cookie;

interface QueueingFactory extends Factory
{
    /**
     * Queue a cookie to send with the next response.
     *
     * @param  mixed
     */
    public function queue();

    /**
     * Remove a cookie from the queue.
     *
     * @param string $name
     */
    public function unqueue(string $name);

    /**
     * Get the cookies which have been queued for the next request.
     *
     * @return array
     */
    public function getQueuedCookies(): array;
}
