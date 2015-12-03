<?php
namespace Viserio\Contracts\Config;

use ArrayAccess;

interface Manager extends ArrayAccess
{
    /**
     * Set Viserio's defaults using the handler.
     *
     * @param array $values
     */
    public function setArray(array $values);

    /**
     * Load the given configuration group.
     *
     * @param string $file
     * @param string $namespace
     * @param string $environment
     * @param string $group
     */

    /**
     * Determine if the given configuration value exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Get a value.
     *
     * @param string $key
     * @param $default
     *
     * @return mixed The value of a setting
     */
    public function get($key, $default = null);

    /**
     * Set a value.
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value);
}
