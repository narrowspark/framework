<?php

namespace Brainwave\Session\Handler;

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

use Brainwave\Contracts\Cache\Repository as CacheContract;

/**
 * CacheBasedSessionHandler.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class CacheBasedSessionHandler implements \SessionHandlerInterface
{
    /**
     * The cache repository instance.
     *
     * @var CacheContract
     */
    protected $cache;

    /**
     * The number of minutes to store the data in the cache.
     *
     * @var int
     */
    protected $minutes;

    /**
     * Create a new cache driven handler instance.
     *
     * @param CacheContract $cache
     * @param int           $minutes
     */
    public function __construct(CacheContract $cache, $minutes)
    {
        $this->cache = $cache;
        $this->minutes = $minutes;
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId)
    {
        return $this->cache->get($sessionId, '');
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        $this->cache->put($sessionId, $data, $this->minutes);
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId)
    {
        $this->cache->forget($sessionId);
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Get the underlying cache repository.
     *
     * @return CacheContract
     */
    public function getCache()
    {
        return $this->cache;
    }
}
