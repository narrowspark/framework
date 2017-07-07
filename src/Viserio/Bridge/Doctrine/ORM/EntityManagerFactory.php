<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Setup;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Viserio\Bridge\Doctrine\ORM\Configuration\CacheManager;
use Viserio\Bridge\Doctrine\ORM\Configuration\MetaDataManager;
use Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class EntityManagerFactory implements
    RequiresComponentConfigIdContract,
    RequiresMandatoryOptionsContract,
    ProvidesDefaultOptionsContract
{
    use ContainerAwareTrait;
    use OptionsResolverTrait;

    /**
     * A MetaDataManager instance.
     *
     * @var \Viserio\Bridge\Doctrine\ORM\Configuration\MetaDataManager
     */
    protected $meta;

    /**
     * A CacheManager instance.
     *
     * @var \Viserio\Bridge\Doctrine\ORM\Configuration\CacheManager
     */
    protected $cache;

    /**
     * A Doctrine Setup instance.
     *
     * @var \Doctrine\ORM\Tools\Setup
     */
    protected $setup;

    /**
     * A EntityListenerResolver instance.
     *
     * @var \Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver
     */
    protected $resolver;

    /**
     * Create a new entity manager factory instance.
     *
     * @param \Interop\Container\ContainerInterface                         $container
     * @param \Doctrine\ORM\Tools\Setup                                     $setup
     * @param \Viserio\Bridge\Doctrine\ORM\Configuration\MetaDataManager    $meta
     * @param \Viserio\Bridge\Doctrine\ORM\Configuration\ConnectionManager  $connection
     * @param \Viserio\Bridge\Doctrine\ORM\Configuration\CacheManager       $cache
     * @param \Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver $resolver
     */
    public function __construct(
        ContainerInterface $container,
        Setup $setup,
        MetaDataManager $meta,
        CacheManager $cache,
        EntityListenerResolver $resolver
    ) {
        $this->container  = $container;
        $this->setup      = $setup;
        $this->meta       = $meta;
        $this->cache      = $cache;
        $this->resolver   = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', 'orm'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'logger' => false,
            'cache'  => [
                'default' => 'array',
            ],
            'events' => [
                'listners'    => false,
                'subscribers' => false,
            ],
            'proxies' => [
                // Auto generate mode possible values are: "NEVER", "ALWAYS", "FILE_NOT_EXISTS", "FILE_OUTDATED", "EVAL"
                'auto_generate' => false,
                'namespace'     => false,
            ],
            'second_level_cache' => false,
            'repository'         => EntityRepository::class,
            'dql'                => [
                'datetime_functions' => [],
                'numeric_functions'  => [],
                'string_functions'   => [],
            ],
            'filters'       => false,
            'mapping_types' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'connections' => [
                'default',
            ],
            'proxies' => [
                'path',
            ],
        ];
    }

    /**
     * Create a new entity manager.
     *
     * @param string $id
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function create(string $id): EntityManagerInterface
    {
        $this->configureOptions($this->container, $id);

        $configuration = $this->setup->createConfiguration(
            $this->options['env'] === 'develop',
            $this->options['proxies']['path'],
            $this->cache->getDriver($this->options['cache']['default'])
        );

        $configuration = $this->setMetadataDriver($configuration);
        $configuration = $this->configureCustomFunctions($configuration);
        $configuration = $this->configureFirstLevelCacheSettings($configuration);
        $configuration = $this->configureProxies($configuration);

        $configuration->setDefaultRepositoryClassName($this->options['repository']);
        $configuration->setEntityListenerResolver($this->resolver);
        $connection = new ConnectionFactory($this->options['']);

        $manager = EntityManager::create(
            $connection->createConnection(
                $this->mapConnectionKey($this->options['connections']['default']),
                $configuration,
                null,
                $this->options['mapping_types']
            ),
            $configuration
        );

        $this->registerLogger($manager, $configuration);
        $this->registerListeners($manager);
        $this->registerSubscribers($manager);
        $this->registerFilters($configuration, $manager);

        return $manager;
    }

    /**
     * Register a logger.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Doctrine\ORM\Configuration          $configuration
     */
    protected function registerLogger(EntityManagerInterface $em, Configuration $configuration)
    {
        if (($loggerClass = $this->options['logger']) !== false) {
            $logger = $this->container->get($loggerClass);
            $logger->register($em, $configuration);
        }
    }

    /**
     * Configure custom functions.
     *
     * @param \Doctrine\ORM\Configuration $configuration
     *
     * @return \Doctrine\ORM\Configuration
     */
    protected function configureCustomFunctions(Configuration $configuration): Configuration
    {
        $configuration->setCustomDatetimeFunctions($this->options['dql']['datetime_functions']);
        $configuration->setCustomNumericFunctions($this->options['dql']['numeric_functions']);
        $configuration->setCustomStringFunctions($this->options['dql']['string_functions']);

        return $configuration;
    }

    /**
     * Configure first level cache.
     *
     * @param \Doctrine\ORM\Configuration $configuration
     *
     * @return \Doctrine\ORM\Configuration
     */
    protected function configureFirstLevelCacheSettings(Configuration $configuration): Configuration
    {
        $cache = $this->cache;

        $configuration->setQueryCacheImpl($cache->getDriver($this->options['query_cache_driver']));
        $configuration->setResultCacheImpl($cache->getDriver($this->options['result_cache_driver']));
        $configuration->setMetadataCacheImpl($cache->getDriver($this->options['metadata_cache_driver']));

        $configuration = $this->setSecondLevelCaching($configuration);

        return $configuration;
    }

    /**
     * Configure second level cache.
     *
     * @param \Doctrine\ORM\Configuration $configuration
     *
     * @return \Doctrine\ORM\Configuration
     */
    protected function setSecondLevelCaching(Configuration $configuration): Configuration
    {
        $secondCacheSetting = $this->options['second_level_cache'];

        if (is_array($secondCacheSetting)) {
            $configuration->setSecondLevelCacheEnabled();

            $cacheConfig = $configuration->getSecondLevelCacheConfiguration();
            // $cacheConfig->setCacheLogger($logger);
            $cacheConfig->setCacheFactory(
                new DefaultCacheFactory(
                    $cacheConfig->getRegionsConfiguration(),
                    $this->cache->getDriver($secondCacheSetting['region_cache_driver'] ?? null)
                )
            );
        }

        return $configuration;
    }

    /**
     * Configure proxies.
     *
     * @param \Doctrine\ORM\Configuration $configuration
     *
     * @return \Doctrine\ORM\Configuration
     */
    protected function configureProxies(Configuration $configuration): Configuration
    {
        $configuration->setProxyDir(
           $this->options['proxies']['path']
        );

        $configuration->setAutoGenerateProxyClasses(
            $this->options['proxies']['auto_generate']
        );

        if ($namespace = $this->options['proxies']['namespace']) {
            $configuration->setProxyNamespace($namespace);
        }
    }

    /**
     * Decorate a entity manager.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    protected function decorateManager(EntityManagerInterface $manager): EntityManagerInterface
    {
        if ($decorator = $this->options['decorator']) {
            if (! class_exists($decorator)) {
                throw new InvalidArgumentException(sprintf('EntityManagerDecorator [%s] does not exist', $decorator));
            }

            $manager = new $decorator($manager);
        }

        return $manager;
    }

    /**
     * Register event listeners.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     *
     * @return void
     */
    protected function registerListeners(EntityManagerInterface $manager): void
    {
        $eventManager = $manager->getEventManager();

        if ($listeners = $this->options['events']['listeners'] !== false) {
            foreach ($listeners as $event => $listener) {
                if (is_array($listener)) {
                    foreach ($listener as $individualListener) {
                        $resolvedListener = $this->container->get($listener);

                        $eventManager->addEventListener($event, $resolvedListener);
                    }
                } else {
                    $resolvedListener = $this->container->get($listener);

                    $eventManager->addEventListener($event, $resolvedListener);
                }
            }
        }
    }

    /**
     * Register event subscribers.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     *
     * @return void
     */
    protected function registerSubscribers(EntityManagerInterface $manager): void
    {
        if ($subscribers = $settings['events']['subscribers'] !== false) {
            foreach ($subscribers as $subscriber) {
                $resolvedSubscriber = $this->container->get($subscriber);
                $manager->getEventManager()->addEventSubscriber($resolvedSubscriber);
            }
        }
    }

    /**
     * Set a metadata driver to doctrine.
     *
     * @param \Doctrine\ORM\Configuration $configuration
     *
     * @return \Doctrine\ORM\Configuration
     */
    protected function setMetadataDriver(Configuration $configuration): Configuration
    {
        $metadata = $this->meta->getDriver($this->options['metadata']['default']);

        $configuration->setMetadataDriverImpl($metadata['driver']);
        $configuration->setClassMetadataFactoryName($metadata['meta_factory']);

        return $configuration;
    }

    /**
     * Register filters.
     *
     * @param \Doctrine\ORM\Configuration          $configuration
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     */
    protected function registerFilters(Configuration $configuration, EntityManagerInterface $manager): void
    {
        if ($filters = $this->options['filters'] !== false) {
            foreach ($filters as $name => $filter) {
                $configuration->addFilter($name, $filter);
                $manager->getFilters()->enable($name);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this;
    }

    /**
     * Map our config style to the dortrine config style.
     *
     * @param array $configs
     *
     * @return array
     */
    private static function mapConnectionKey(array $configs): array
    {
        $mapList = [
            'dbname' => 'database',
            'user'   => 'username',
        ];

        foreach ($mapList as $newKey => $oldKey) {
            if ($configs[$oldKey]) {
                $arr[$newKey] = $arr[$oldKey];
                unset($arr[$oldKey]);
            }
        }

        return $configs;
    }
}
