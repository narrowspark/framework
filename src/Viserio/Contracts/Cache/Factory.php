<?php
namespace Viserio\Contracts\Cache;

/**
 * Factory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
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
