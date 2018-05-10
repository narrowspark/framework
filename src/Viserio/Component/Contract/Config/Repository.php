<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Config;

use ArrayAccess;

interface Repository extends ArrayAccess
{
    /**
     * Add a parameter processor.
     *
     * @param \Viserio\Component\Contract\Config\ParameterProcessor $parameterProcessor
     *
     * @return $this
     */
    public function addParameterProcessor(ParameterProcessor $parameterProcessor): self;

    /**
     * Get all registered parameter processors.
     *
     * @return array
     */
    public function getParameterProcessors(): array;

    /**
     * Import configuration from file.
     *
     * @param string     $filePath
     * @param null|array $options  Supports tag or group
     *
     * @throws \Viserio\Component\Contract\Config\Exception\FileNotFoundException if the php file was not found
     * @throws \Viserio\Component\Contract\Parser\Exception\ParseException
     *
     * @return $this
     */
    public function import(string $filePath, array $options = null): self;

    /**
     * Setting configuration values, using
     * either simple or nested keys.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set(string $key, $value): self;

    /**
     * Gets a configuration setting using a simple or nested key.
     *
     * @param string     $key
     * @param null|mixed $default
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
    public function delete(string $key): self;

    /**
     * Set an array of configuration options
     * Merge provided values with the defaults to ensure all required values are set.
     *
     * @param array $values
     * @param bool  $processed should only be true, if array is preprocessed
     *
     * @return $this
     */
    public function setArray(array $values = [], bool $processed = false): self;

    /**
     * Get all values as nested array.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Get all values as processed nested array.
     *
     * @return array
     */
    public function getAllProcessed(): array;

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
