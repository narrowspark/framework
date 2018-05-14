<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Provider;

use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\StaticalProxy\AliasLoader as AliasLoaderContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\StaticalProxy\AliasLoader;

class AliasLoaderServiceProvider implements
    ServiceProviderContract,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            AliasLoaderContract::class => [self::class, 'createAliasLoader'],
            AliasLoader::class         => function (ContainerInterface $container) {
                return $container->get(AliasLoaderContract::class);
            },
            'alias' => function (ContainerInterface $container) {
                return $container->get(AliasLoaderContract::class);
            },
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
    public static function getDimensions(): array
    {
        return ['viserio', 'staticalproxy'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'aliases'         => [],
            'cache_path'      => null,
            'real_time_proxy' => false,
        ];
    }

    /**
     * Create a new Alias loader.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\StaticalProxy\AliasLoader
     */
    public static function createAliasLoader(ContainerInterface $container): AliasLoaderContract
    {
        $options = self::resolveOptions($container->get('config'));

        $loader    = new AliasLoader($options['aliases']);
        $cachePath = self::getCachePath($container, $options);

        if ($cachePath !== null) {
            $loader->setCachePath($cachePath);

            if ($options['real_time_proxy'] === true) {
                $loader->enableRealTimeStaticalProxy();
            }
        }

        return $loader;
    }

    /**
     * Get real-time proxy cache path.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param array                             $options
     *
     * @return null|string
     */
    private static function getCachePath(ContainerInterface $container, array $options): ?string
    {
        $cachePath = $options['cache_path'];

        if ($cachePath === null && $container->has(KernelContract::class)) {
            $cachePath = $container->get(KernelContract::class)->getStoragePath('staticalproxy');
        }

        return $cachePath;
    }
}
