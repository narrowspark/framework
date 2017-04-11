<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\StaticalProxy\AliasLoader as AliasLoaderContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\StaticalProxy\AliasLoader;

class AliasLoaderServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options = [];

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

    public static function createAliasLoader(ContainerInterface $container): AliasLoader
    {
        self::resolveOptions($container);

        return new AliasLoader(self::$options['aliases']);
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
        if (count(self::$options) === 0) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
