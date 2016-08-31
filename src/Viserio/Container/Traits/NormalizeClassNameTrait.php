<?php
declare(strict_types=1);
namespace Viserio\Container\Traits;

trait NormalizeClassNameTrait
{
    /**
     * Normalize the given class name by removing leading slashes.
     *
     * @param mixed $service
     *
     * @return mixed
     */
    protected function normalize($service)
    {
        return is_string($service) ? ltrim($service, '\\') : $service;
    }
}
