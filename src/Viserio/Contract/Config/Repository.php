<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Config;

use ArrayAccess;

interface Repository extends ArrayAccess
{
    /**
     * Add a parameter processor.
     *
     * @param \Viserio\Contract\Config\ParameterProcessor $parameterProcessor
     *
     * @return self
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
     * @throws \Viserio\Contract\Config\Exception\FileNotFoundException if the php file was not found
     * @throws \Viserio\Contract\Parser\Exception\ParseException
     *
     * @return self
     */
    public function import(string $filePath, array $options = null): self;

    /**
     * Setting configuration values, using
     * either simple or nested keys.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return self
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
     * @return self
     */
    public function delete(string $key): self;

    /**
     * Set an array of configuration options
     * Merge provided values with the defaults to ensure all required values are set.
     *
     * @param array $values
     *
     * @return self
     */
    public function setArray(array $values = []): self;

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
