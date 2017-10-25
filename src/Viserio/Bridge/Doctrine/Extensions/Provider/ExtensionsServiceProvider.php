<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Provider;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Gedmo\DoctrineExtensions;
use Interop\Container\ServiceProviderInterface;
use LaravelDoctrine\Fluent\Extensions\GedmoExtensions;
use LaravelDoctrine\Fluent\FluentDriver;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ExtensionsServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', 'extensions'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'all_mappings' => false,
        ];
    }

    /**
     * Check if a annotations driver exists.
     *
     * @param \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain $chain
     *
     * @return bool
     */
    private static function hasAnnotationReader(MappingDriverChain $chain): bool
    {
        foreach ($chain->getDrivers() as $driver) {
            if ($driver instanceof AnnotationDriver) {
                return true;
            }
        }

        return false;
    }

    /**
     * Register gedmo for annotations.
     *
     * @param \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain $chain
     *
     * @return void
     */
    private static function registerGedmoForAnnotations(MappingDriverChain $chain): void
    {
        if (self::needsAllMappings()) {
            DoctrineExtensions::registerMappingIntoDriverChainORM(
                $chain,
                $chain->getReader()
            );
        } else {
            DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
                $chain,
                $chain->getReader()
            );
        }
    }

    /**
     * Check if a fluent driver exists.
     *
     * @param \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain $chain
     *
     * @return bool
     */
    private static function hasFluentDriver(MappingDriverChain $chain): bool
    {
        foreach ($chain->getDrivers() as $driver) {
            if ($driver instanceof FluentDriver) {
                return true;
            }
        }

        return false;
    }

    /**
     * Register gedmo for fluent.
     *
     * @param \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain $chain
     *
     * @return void
     */
    private static function registerGedmoForFluent(MappingDriverChain $chain): void
    {
        if (self::needsAllMappings()) {
            GedmoExtensions::registerAll($chain);
        } else {
            GedmoExtensions::registerAbstract($chain);
        }
    }

    /**
     * Check if all mappings are needed.
     *
     * @return bool
     */
    private static function needsAllMappings(): bool
    {
        return self::$options['all_mappings'] === true;
    }
}
