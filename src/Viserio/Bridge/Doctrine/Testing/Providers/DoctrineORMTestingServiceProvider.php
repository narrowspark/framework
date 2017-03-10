<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Providers;

use Doctrine\Common\Persistence\ManagerRegistry;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Interop\Container\ServiceProvider;
use Viserio\Bridge\Doctrine\Testing\Factory as EntityFactory;

class DoctrineORMTestingServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            EntityFactory::class  => [self::class, 'createEntityFactory'],
            FakerGenerator::class => [self::class, 'createFakerGenerator'],
        ];
    }

    public static function createEntityFactory(ContainerInterface $container): EntityFactory
    {
        return new EntityFactory(
            $container->get(FakerGenerator::class),
            $container->get(ManagerRegistry::class)
        );
    }

    public static function createFakerGenerator(): FakerFactory
    {
        return FakerFactory::create();
    }
}
