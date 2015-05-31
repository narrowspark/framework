<?php

namespace Brainwave\Session;

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
 * Flash.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Flash
{
    /**
     * Store instance.
     *
     * @var \Brainwave\Session\Store
     */
    protected $store;

    /**
     * Create a new flash instance.
     *
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Age the flash data for the session.
     */
    public function ageFlashData()
    {
        foreach ($this->store->get('flash.old', []) as $old) {
            $this->store->forget($old);
        }
        $this->store->put('flash.old', $this->store->get('flash.new', []));
        $this->store->put('flash.new', []);
    }

    /**
     * Flash a key / value pair to the session.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function flash($key, $value)
    {
        $this->store->put($key, $value);
        $this->store->push('flash.new', $key);

        $this->removeFromOldFlashData([$key]);
    }

    /**
     * Flash an input array to the session.
     *
     * @param array $value
     */
    public function flashInput(array $value)
    {
        $this->flash('_old_input', $value);
    }

    /**
     * Reflash all of the session flash data.
     */
    public function reflash()
    {
        $this->mergeNewFlashes($this->store->get('flash.old', []));
        $this->store->put('flash.old', []);
    }

    /**
     * Reflash a subset of the current flash data.
     *
     * @param array|mixed $keys
     */
    public function keep($keys = null)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $this->mergeNewFlashes($keys);
        $this->removeFromOldFlashData($keys);
    }

    /**
     * Merge new flash keys into the new flash array.
     *
     * @param array $keys
     */
    protected function mergeNewFlashes(array $keys)
    {
        $values = array_unique(array_merge($this->store->get('flash.new', []), $keys));
        $this->store->put('flash.new', $values);
    }

    /**
     * Remove the given keys from the old flash data.
     *
     * @param array $keys
     */
    protected function removeFromOldFlashData(array $keys)
    {
        $this->store->put('flash.old', array_diff($this->store->get('flash.old', []), $keys));
    }
}
