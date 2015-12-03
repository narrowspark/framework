<?php
namespace Viserio\Contracts\Config;

/**
 * Repository.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Repository extends \ArrayAccess
{
    /**
     * Set an array of configuration options
     * Merge provided values with the defaults to ensure all required values are set.
     *
     * @param array $values
     * @required
     */
    public function setArray(array $values = []);

    /**
     * Get all values as nested array.
     *
     * @return array
     */
    public function getAllNested();

    /**
     * Get all values as flattened key array.
     *
     * @return array
     */
    public function getAllFlat();

    /**
     * Get all flattened array keys.
     *
     * @return array
     */
    public function getKeys();
}
