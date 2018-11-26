<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Bridge\Doctrine\DBAL\ConnectionManager;
use Viserio\Bridge\Doctrine\Testing\DBAL\StaticConnectionManager;

class DoctrineTestingServiceProvier implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            ConnectionManager::class => [self::class, 'createStaticConnectionManager'],
        ];
    }

    /**
     * Create a new instance of faker generator.
     *
     * @param \Psr\Container\ContainerInterface                    $container
     * @param null|\Viserio\Bridge\Doctrine\DBAL\ConnectionManager $connectionManager
     *
     * @return \Viserio\Bridge\Doctrine\Testing\DBAL\StaticConnectionManager
     */
    public static function createStaticConnectionManager(
        ContainerInterface $container,
        ?ConnectionManager $connectionManager
    ): StaticConnectionManager {
        $manager = new StaticConnectionManager($container);

        if ($connectionManager !== null) {
            $manager->setDoctrineConfiguration($manager->getDoctrineConfiguration());
            $manager->setDoctrineEventManager($manager->getDoctrineEventManager());
        }

        return $manager;
    }
}
