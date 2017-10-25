<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Bridge\Doctrine\Testing\Factory as EntityFactory;

class DoctrineORMTestingServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories()
    {
        return [
            EntityFactory::class  => [self::class, 'createEntityFactory'],
            FakerGenerator::class => [self::class, 'createFakerGenerator'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'doctrine'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'locale' => FakerFactory::DEFAULT_LOCALE
        ];
    }

    /**
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return EntityFactory
     */
    public static function createEntityFactory(ContainerInterface $container): EntityFactory
    {
        return new EntityFactory(
            $container->get(FakerGenerator::class),
            $container->get(ManagerRegistry::class)
        );
    }

    /**
     * Create a new instance of faker generator.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Faker\Generator
     */
    public static function createFakerGenerator(ContainerInterface $container): FakerGenerator
    {
        return FakerFactory::create();
    }
}
