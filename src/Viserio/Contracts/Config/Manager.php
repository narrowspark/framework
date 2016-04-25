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
     * Checking if configuration values exist, using
     * either simple or nested keys.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Gets a configuration setting using a simple or nested key.
     *
     * @param string $key
     * @param $default
     *
     * @return mixed The value of a setting
     */
    public function get($key, $default = null);

    /**
     * Setting configuration values, using
     * either simple or nested keys.
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value);
}
