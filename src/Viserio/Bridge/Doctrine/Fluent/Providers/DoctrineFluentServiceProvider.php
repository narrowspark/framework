<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Fluent\Providers;

use Doctrine\ORM\Configuration;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use LaravelDoctrine\Fluent\FluentDriver;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class DoctrineFluentServiceProvider implements
    ServiceProvider,
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options;

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'mappings' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            FluentDriver::class  => [self::class, 'createFluentDriver'],
            Configuration::class => [self::class, 'createConfiguration'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', 'fluent'];
    }

    /**
     * Create a new fluent driver.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return \LaravelDoctrine\Fluent\FluentDriver
     */
    public static function createFluentDriver(ContainerInterface $container): FluentDriver
    {
        self::resolveOptions($container);

        return new FluentDriver(self::$options['mappings']);
    }

    /**
     * Extend doctrine configuration.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Doctrine\ORM\Configuration
     */
    public static function createConfiguration(ContainerInterface $container, ?callable $getPrevious = null): ?Configuration
    {
        if ($getPrevious !== null) {
            $config = $getPrevious();
            $config->setMetadataDriverImpl($container->get(FluentDriver::class));

            return $config;
        }

        return null;
    }

    /**
     * Resolve component options.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
