<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Twig_LexerInterface;
use Twig_Loader_Array;
use Twig_Loader_Chain;
use Twig_LoaderInterface;
use Viserio\Bridge\Twig\Extensions\DumpExtension;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Bridge\Twig\TwigEnvironment;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class TwigBridgeServiceProvider implements ServiceProvider, RequiresComponentConfigContract, RequiresMandatoryOptionsContract
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
    public function getServices()
    {
        return [
            TwigLoader::class           => [self::class, 'createTwigLoader'],
            Twig_LoaderInterface::class => function (ContainerInterface $container) {
                return $container->get(TwigLoader::class);
            },
            TwigEnvironment::class      => [self::class, 'createTwigEnvironment'],
            FactoryContract::class      => [self::class, 'createViewFactory'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'paths',
            'engines' => [
                'twig' => [
                    'options' => [
                        'debug',
                    ],
                ],
            ],
        ];
    }

    public static function createViewFactory(ContainerInterface $container): FactoryContract
    {
        $view = $container->get(FactoryContract::class);

        $view->addExtension('twig.html', 'twig');

        return $view;
    }

    public static function createTwigEnvironment(ContainerInterface $container): TwigEnvironment
    {
        self::resolveOptions($container);

        $options = self::$options['engines']['twig']['options'];

        $twig = new TwigEnvironment(
            $container->get(Twig_LoaderInterface::class),
            $options
        );

        if ($container->has(Twig_LexerInterface::class)) {
            $twig->setLexer($container->get(Twig_LexerInterface::class));
        }

        if ($options['debug']) {
            $twig->addExtension(new DumpExtension());
        }

        return $twig;
    }

    /**
     * Create a twig bridge loader.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return \Twig_LoaderInterface
     */
    public static function createTwigLoader(ContainerInterface $container): Twig_LoaderInterface
    {
        self::resolveOptions($container);

        $options = self::$options['engines']['twig'];

        $loaders = [
            new TwigLoader(
                $container->get(FilesystemContract::class),
                $container->get(FinderContract::class)
            ),
        ];

        if (isset($options['file_extension'])) {
            $loaders->setExtension($options['file_extension']);
        }

        if (isset($options['templates']) && is_array($options['templates'])) {
            $loaders[] = new Twig_Loader_Array($options['templates']);
        }

        if (isset($options['loaders']) && is_array($options['loaders'])) {
            $loaders = array_merge($loaders, $options['loaders']);
        }

        return new Twig_Loader_Chain($loaders);
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
            self::$options   = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
