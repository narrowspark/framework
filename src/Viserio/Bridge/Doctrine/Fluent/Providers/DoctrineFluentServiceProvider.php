<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Fluent\Providers;

use Doctrine\DBAL\Configuration;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use LaravelDoctrine\Fluent\FluentDriver;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class DoctrineFluentServiceProvider implements ServiceProvider, RequiresComponentConfigContract
{
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

    public static function createFluentDriver(ContainerInterface $container): FluentDriver
    {
        self::resolveOptions(container);

        return new FluentDriver(self::$options['mappings'] ?? []);
    }

    public static function createConfiguration(ContainerInterface $container, callable $getPrevious): Configuration
    {
        $config = $getPrevious();
        $config->setMetadataDriverImpl($fluent);

        return $config;
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
