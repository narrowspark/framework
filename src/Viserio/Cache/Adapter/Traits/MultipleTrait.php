<?php
namespace Viserio\Cache\Adapter\Traits;

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
