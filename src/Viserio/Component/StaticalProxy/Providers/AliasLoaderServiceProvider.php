<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\StaticalProxy\AliasLoader as AliasLoaderContract;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;
use Viserio\Component\StaticalProxy\AliasLoader;

class AliasLoaderServiceProvider implements
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
    public function getDimensions(): iterable
    {
        return ['viserio', 'staticalproxy'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'aliases' => [],
        ];
    }

    /**
     * Create a new Alias loader.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contracts\StaticalProxy\AliasLoader
     */
    public static function createAliasLoader(ContainerInterface $container): AliasLoaderContract
    {
        $options = self::resolveOptions($container);

        return new AliasLoader($options['aliases']);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
