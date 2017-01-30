<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Twig_LexerInterface;
use Twig_Loader_Array;
use Twig_LoaderInterface;
use Viserio\Bridge\Twig\Extensions\DumpExtension;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Bridge\Twig\TwigEnvironment;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Support\Traits\CreateOptionsTrait;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;

class TwigBridgeServiceProvider implements ServiceProvider, RequiresConfig, RequiresMandatoryOptions
{
    use ConfigurationTrait;
    use CreateOptionsTrait;

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
    public function dimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions(): iterable
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
        if (count(self::$options) === 0) {
            self::createOptions($container);
        }

        $options  = self::$options['engines']['twig']['options'];

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
        if (count(self::$options) === 0) {
            self::createOptions($container);
        }

        $config  = self::$options['engines']['twig'];

        $loaders = [
            new TwigLoader(
                $container->get(FilesystemContract::class),
                $container->get(FinderContract::class)
            ),
        ];

        if (isset($config['file_extension'])) {
            $loaders->setExtension($config['file_extension']);
        }

        if (isset($config['templates']) && is_array($config['templates'])) {
            $loaders[] = new Twig_Loader_Array($config['templates']);
        }

        if (isset($config['loaders']) && is_array($config['loaders'])) {
            $loaders = array_merge($loaders, $config['loaders']);
        }

        return new Twig_Loader_Chain($loaders);
    }
}
