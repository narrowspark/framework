<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Providers;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Gedmo\DoctrineExtensions;
use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use LaravelDoctrine\Fluent\Extensions\GedmoExtensions;
use LaravelDoctrine\Fluent\FluentDriver;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class ExtensionsServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use StaticOptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Application::class => [self::class, 'registerExtensions'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', 'gedmo'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'all_mappings' => false,
        ];
    }

    /**
     * Register some doctrine extensions.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function registerExtensions(ContainerInterface $container, ?callable $getPrevious = null): ?Application
    {
        if ($getPrevious !== null) {
            self::resolveOptions($container);

            $events = $getPrevious();

            return $events;
        }

        return null;
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
        return self::$options['all_mappings'] !== false;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
