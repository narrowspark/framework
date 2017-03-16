<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Configuration;

use Cache\Bridge\Doctrine\DoctrineCacheBridge;
use Doctrine\Common\Cache\Cache;
use Viserio\Component\Cache\CacheManager as BaseCacheManager;

class CacheManager extends BaseCacheManager
{
    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', $this->getConfigName()];
    }

    /**
     * {@inheritdoc}
     */
    public function createDriver(array $config)
    {
        $driver = parent::createDriver($config);

        if ($driver instanceof Cache) {
            return $driver;
        }

        return new DoctrineCacheBridge($driver);
    }
}
