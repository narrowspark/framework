<?php
namespace Viserio\Cache\Store;

abstract class TaggableStore
{
    /**
     * Begin executing a new tags operation.
     *
     * @param string $name
     *
     * @return \Viserio\Cache\Store\TaggedCache
     */
    public function section($name)
    {
        return $this->tags($name);
    }

    /**
     * Begin executing a new tags operation.
     *
     * @param string $names
     *
     * @return \Viserio\Cache\Store\TaggedCache
     */
    public function tags($names)
    {
        return new TaggedCache(
            $this,
            new TagSet($this, is_array($names) ? $names : func_get_args())
        );
    }
}
