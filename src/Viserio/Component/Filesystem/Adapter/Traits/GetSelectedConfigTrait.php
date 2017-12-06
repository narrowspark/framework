<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter\Traits;

trait GetSelectedConfigTrait
{
    /**
     * Get a subset of the items from the given array.
     *
     * @param array    $config
     * @param string[] $keys
     *
     * @return string[]
     */
    protected static function getSelectedConfig(array $config, array $keys): array
    {
        return \array_intersect_key($config, \array_flip($keys));
    }
}
