<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container;

interface TaggableServiceProvider extends ServiceProvider
{
    /**
     * Returns a list of all tagged container entries registered by this service provider.
     *
     * - the key is the tag name
     * - the value is a array of the tagged container entries by this service provider.
     *
     * @return array
     */
    public function getTags(): array;
}
