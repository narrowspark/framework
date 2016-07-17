<?php
namespace Viserio\Contracts\Config;

use ArrayAccess;

interface Repository extends ArrayAccess
{
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
    public function getAllNested(): array;

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
