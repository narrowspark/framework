<?php
namespace Viserio\Cache\Store;

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
 * TaggableStore.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
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
