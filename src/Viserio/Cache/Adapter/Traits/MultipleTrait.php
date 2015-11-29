<?php
namespace Viserio\Cache\Adapter\Traits;
/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

/**
 * MultipleTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
trait MultipleTrait
{
    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value for the key.
     *
     * @param array $keys
     *
     * @return array
     */
    public function getMultiple(array $keys)
    {
        $returnValues = [];

        foreach ($keys as $singleKey) {
            $returnValues[$singleKey] = $this->get($singleKey);
        }

        return $returnValues;
    }

    /**
     * Store multiple items in the cache for a set number of minutes.
     *
     * @param array $values
     * @param int   $minutes
     */
    public function putMultiple(array $values, $minutes)
    {
        foreach ($values as $key => $singleValue) {
            $this->put($key, $singleValue, $minutes);
        }
    }
}
