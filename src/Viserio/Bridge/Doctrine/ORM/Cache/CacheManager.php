<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Cache;

use Doctrine\Common\Cache\ArrayCache;
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
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'cache';
    }
}
