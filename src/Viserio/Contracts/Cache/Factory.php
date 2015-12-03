<?php
namespace Viserio\Contracts\Cache;

interface Factory
{
    /**
     * Builder.
     *
     * @param string $driver  The cache driver to use
     * @param array  $options
     *
     * @return mixed
     */
    public function driver($driver, array $options = []);
}
