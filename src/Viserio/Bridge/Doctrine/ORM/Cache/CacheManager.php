<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Cache;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CouchbaseCache;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Support\AbstractManager;

class CacheManager extends AbstractManager implements ProvidesDefaultOptionsContract
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'default' => 'array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', $this->getConfigName()];
    }

    /**
     * Create an instance of the Apc cache driver.
     *
     * @param array $config
     *
     * @return \Doctrine\Common\Cache\ApcCache
     */
    protected function createApcDriver(array $config): ApcCache
    {
        return new ApcCache();
    }

    /**
     * Create an instance of the Apcu cache driver.
     *
     * @param array $config
     *
     * @return \Doctrine\Common\Cache\ApcuCache
     */
    protected function createApcuDriver(array $config): ApcuCache
    {
        return new ApcuCache();
    }

    /**
     * Create an instance of the Array cache driver.
     *
     * @param array $config
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function createArrayDriver(array $config): ArrayCache
    {
        return new ArrayCache();
    }

    /**
     * Create an instance of the Array cache driver.
     *
     * @param array $config
     *
     * @return \Doctrine\Common\Cache\CouchbaseCache
     */
    protected function createCouchbaseDriver(array $config): CouchbaseCache
    {
        return new CouchbaseCache();
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'cache';
    }
}
