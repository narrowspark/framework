<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Setup;
use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver;

class EntityManagerFactory
{
    use ContainerAwareTrait;

    /**
     * @var MetaDataManager
     */
    protected $meta;

    /**
     * @var ConnectionManager
     */
    protected $connection;

    /**
     * @var CacheManager
     */
    protected $cache;

    /**
     * @var \Doctrine\ORM\Tools\Setup
     */
    private $setup;

    /**
     * @var \Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver
     */
    private $resolver;

    /**
     * Create a new manager registry instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
