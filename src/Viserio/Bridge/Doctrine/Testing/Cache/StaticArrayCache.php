<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Cache;

use Doctrine\Common\Cache\CacheProvider;

class StaticArrayCache extends CacheProvider
{
    /**
     * List of saved data.
     * Each element being a tuple of [$data, $expiration], where the expiration is int|bool.
     *
     * @var array
     */
    private static $data = [];

    /**
     * @var int
     */
    private $hitsCount = 0;

    /**
     * @var int
     */
    private $missesCount = 0;

    /**
     * @var int
     */
    private $upTime;

    /**
     * Create a new static array instance.
     */
    public function __construct()
    {
        $this->upTime = time();
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        if (! $this->doContains($id)) {
            $this->missesCount++;

            return false;
        }

        $this->hitsCount++;

        return self::$data[$id][0];
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id): bool
    {
        if (! isset(self::$data[$id])) {
            return false;
        }

        $expiration = self::$data[$id][1];

        if ($expiration && $expiration < time()) {
            $this->doDelete($id);

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0): bool
    {
        self::$data[$id] = [$data, $lifeTime ? time() + $lifeTime : false];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id): bool
    {
        unset(self::$data[$id]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush(): bool
    {
        self::$data = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats(): ?array
    {
        return [
            Cache::STATS_HITS             => $this->hitsCount,
            Cache::STATS_MISSES           => $this->missesCount,
            Cache::STATS_UPTIME           => $this->upTime,
            Cache::STATS_MEMORY_USAGE     => null,
            Cache::STATS_MEMORY_AVAILABLE => null,
        ];
    }
}
