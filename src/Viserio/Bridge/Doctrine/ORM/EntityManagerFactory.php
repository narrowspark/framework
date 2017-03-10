<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Setup;
use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver;
use Viserio\Bridge\Doctrine\ORM\Configuration\MetaData\MetaDataManager;
use Viserio\Component\OptionsResolver\Traits\ConfigurationTrait;

class EntityManagerFactory
{
    use ContainerAwareTrait;
    use ConfigurationTrait;

    /**
     * @var \Viserio\Bridge\Doctrine\ORM\Configuration\MetaData\MetaDataManager
     */
    protected $meta;

    /**
     * @var ConnectionManager
     */
    protected $connection;

    /**
     * @var \Viserio\Component\Contracts\Cache\Manager
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
    public function __construct(
        ContainerInterface $container,
        Setup $setup,
        MetaDataManager $meta,
        CacheManagerContract $cache,
        EntityListenerResolver $resolver
    ) {
        $this->container = $container;

        $this->configureOptions($this->container);
    }
}
