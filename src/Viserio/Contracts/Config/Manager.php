<?php
namespace Viserio\Contracts\Config;

use ArrayAccess;

interface Manager extends ArrayAccess
{
    /**
     * Setting configuration values, using
     * either simple or nested keys.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function set(string $key, $value): Manager;

    /**
     * Gets a configuration setting using a simple or nested key.
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed The value of a setting
     */
    public function get(string $key, $default = null);

    /**
     * Checking if configuration values exist, using
     * either simple or nested keys.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Forget a key and all his values.
     *
     * @param string $key
     */
    public function forget(string $key);
}
