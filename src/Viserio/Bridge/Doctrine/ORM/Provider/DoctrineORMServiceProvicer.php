<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Provider;

use Doctrine\Common\Persistence\ManagerRegistry as DoctrineManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Bridge\Doctrine\ORM\ManagerRegistry;

class DoctrineORMServiceProvicer implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            DoctrineManagerRegistry::class => [self::class, 'createManagerRegistry'],
            ManagerRegistry::class         => function (ContainerInterface $container) {
                return $container->get(DoctrineManagerRegistry::class);
            },
            'registry'                     => function (ContainerInterface $container) {
                return $container->get(DoctrineManagerRegistry::class);
            },
            EntityManagerInterface::class => [self::class, 'createEntityManager'],
            EntityManager::class          => function (ContainerInterface $container) {
                return $container->get(EntityManagerInterface::class);
            },
            'em'                          => function (ContainerInterface $container) {
                return $container->get(EntityManagerInterface::class);
            },
        ];
    }

    public static function createManagerRegistry(ContainerInterface $container): DoctrineManagerRegistry
    {
        $registery = new ManagerRegistry($container, $container->get(EntityManagerFactory::class));

        return $registery;
    }

    public static function createEntityManager(ContainerInterface $container): EntityManagerInterface
    {
        return $container->get(DoctrineManagerRegistry::class)->getManager();
    }
}
