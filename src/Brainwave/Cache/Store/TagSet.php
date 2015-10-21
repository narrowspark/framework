<?php

namespace Brainwave\Cache\Store;

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

use Brainwave\Contracts\Cache\Adapter as AdapterContract;

/**
 * TagSet.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class TagSet
{
    /**
     * The cache store implementation.
     *
     * @var \Brainwave\Contracts\Cache\Adapter
     */
    protected $store;

    /**
     * The tag names.
     *
     * @var array
     */
    protected $names = [];

    /**
     * Create a new TagSet instance.
     *
     * @param \Brainwave\Contracts\Cache\Adapter $store
     * @param array                              $names
     */
    public function __construct(AdapterContract $store, array $names = [])
    {
        $this->store = $store;
        $this->names = $names;
    }

    /**
     * Reset all tags in the set.
     */
    public function reset()
    {
        array_walk($this->names, [$this, 'resetTag']);
    }

    /**
     * Get the unique tag identifier for a given tag.
     *
     * @param string $name
     *
     * @return string
     */
    public function tagId($name)
    {
        return $this->store->get($this->tagKey($name)) ?: $this->resetTag($name);
    }

    /**
     * Get an array of tag identifiers for all of the tags in the set.
     *
     * @return array
     */
    protected function tagIds()
    {
        return array_map([$this, 'tagId'], $this->names);
    }

    /**
     * Get a unique namespace that changes when any of the tags are flushed.
     *
     * @return string
     */
    public function getNamespace()
    {
        return implode('|', $this->tagIds());
    }

    /**
     * Reset the tag and return the new tag identifier.
     *
     * @param string $name
     *
     * @return string
     */
    public function resetTag($name)
    {
        $this->store->forever($this->tagKey($name), $id = str_replace('.', '', uniqid('', true)));

        return $id;
    }

    /**
     * Get the tag identifier key for a given tag.
     *
     * @param string $name
     *
     * @return string
     */
    public function tagKey($name)
    {
        return 'tag:'.$name.':key';
    }
}
