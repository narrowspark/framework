<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Doctrine\ORM\Tools\Setup;
use Interop\Container\ContainerInterface;
use Viserio\Bridge\Doctrine\ORM\Configuration\MetaData\MetaDataManager;
use Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver;
use Viserio\Component\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\ConfigurationTrait;

class EntityManagerFactory implements
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use ContainerAwareTrait;
    use ConfigurationTrait;

    /**
     * @var \Viserio\Bridge\Doctrine\ORM\Configuration\MetaData\MetaDataManager
     */
    protected $meta;

    /**
     * @var \Viserio\Component\Contracts\Cache\Manager
     */
    protected $cache;

    /**
     * @var \Doctrine\ORM\Tools\Setup
     */
    protected $setup;

    /**
     * @var \Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver
     */
    protected $resolver;

    /**
     * Create a new manager registry instance.
     *
     * @param \Interop\Container\ContainerInterface                               $container
     * @param \Doctrine\ORM\Tools\Setup                                           $setup
     * @param \Viserio\Bridge\Doctrine\ORM\Configuration\MetaData\MetaDataManager $meta
     * @param \Viserio\Component\Contracts\Cache\Manager                          $cache
     * @param \Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver       $resolver
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

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return ['connections'];
    }
}
