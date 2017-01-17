<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handler;

use Cache\SessionHandler\Psr6SessionHandler;
use Psr\Cache\CacheItemPoolInterface;
use SessionHandlerInterface;

class CacheBasedSessionHandler implements SessionHandlerInterface
{
    /**
     * The cache repository instance.
     *
     * @var \Cache\SessionHandler\Psr6SessionHandler
     */
    protected $psr6cache;

    /**
     * Create a new cache driven handler instance.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param int                               $lifetime
     */
    public function __construct(CacheItemPoolInterface $cache, int $lifetime)
    {
        $this->psr6cache = new Psr6SessionHandler($cache, ['ttl' => $lifetime]);
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * // @codeCoverageIgnore
     */
    public function read($sessionId)
    {
        return $this->psr6cache->read($sessionId);
    }

    /**
     * {@inheritdoc}
     *
     * // @codeCoverageIgnore
     */
    public function write($sessionId, $data)
    {
        $this->psr6cache->write($sessionId, $data);
    }

    /**
     * {@inheritdoc}
     *
     * // @codeCoverageIgnore
     */
    public function destroy($sessionId)
    {
        $this->psr6cache->destroy($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        return true;
    }
}
