<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Config;

use ArrayAccess;

interface Repository extends ArrayAccess
{
    /**
     * Import configuation from file.
     *
     * @param string      $file
     * @param array|null  $options Supports tag or group
     *
     * @throws \RuntimeException                                             If the php file is not found.
     * @throws \Viserio\Component\Contracts\Parsers\Exception\ParseException
     *
     * @return $this
     */
    public function import(string $file, array $options = null): Repository;

    /**
     * Setting configuration values, using
     * either simple or nested keys.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set(string $key, $value): Repository;

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
     * delete a key and all his values.
     *
     * @param string $key
     *
     * @return $this
     */
    public function delete(string $key): Repository;

    /**
     * Set an array of configuration options
     * Merge provided values with the defaults to ensure all required values are set.
     *
     * @param array $values
     *
     * @return $this
     */
    public function setArray(array $values = []): Repository;

    /**
     * Get all values as nested array.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Get all values as flattened key array.
     *
     * @return array
     */
    public function getAllFlat(): array;

    /**
     * Get all flattened array keys.
     *
     * @return array
     */
    public function getKeys(): array;
}
