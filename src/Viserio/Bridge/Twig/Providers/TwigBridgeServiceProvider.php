<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Bridge\Twig\Engine\TwigEngine;
use Viserio\Component\View\Engines\EngineResolver;
use Twig_Environment;
use Twig_LexerInterface;
use Twig_Loader_Array;
use Twig_LoaderInterface;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Bridge\Twig\TwigEnvironment;
use Viserio\Bridge\Twig\Extensions\DumpExtension;
use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;
use Viserio\Component\Contracts\Support\Traits\CreateConfigurationTrait;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\Contracts\View\Factory as FactoryContract;

class TwigBridgeServiceProvider implements ServiceProvider, RequiresConfig, RequiresMandatoryOptions
{
    use ConfigurationTrait;
    use CreateConfigurationTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            TwigEnvironment::class => [self::class, 'createTwigEnvironment'],
            FactoryContract::class  => [self::class, 'createViewFactory'],
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
                        'debug'
                    ]
                ]
            ],
        ];
    }

    public static function createViewFactory(ContainerInterface $container): FactoryContract
    {
        $view = $container->get(FactoryContract::class);

        $view->addExtension('twig.html', 'twig');

        return $view;
    }

    public function createTwigEnvironment(ContainerInterface $container): TwigEnvironment
    {
        $this->createConfiguration($container);

        $config  = $this->config['engines']['twig'];
        $options = $config['options'];

        $twig = new TwigEnvironment(
            self::createTwigLoader($container, $config),
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
     * [createTwigLoader description]
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param  array                                $config
     *
     * @return \Twig_LoaderInterface
     */
    protected static function createTwigLoader(ContainerInterface $container, array $config): Twig_LoaderInterface
    {
        $loaders = [
            new TwigLoader(
                $container->get(FilesystemContract::class),
                $container->get(FinderContract::class),
                $config['file_extension'] ?? 'twig'
            ),
        ];

        if (isset($config['templates']) && is_array($config['templates'])) {
            $loaders[] = new Twig_Loader_Array($config['templates']);
        }

        if (isset($config['loader']) && is_array($config['loader'])) {
            $loaders = array_merge($loaders, $config['loader']);
        }

        return new Twig_Loader_Chain($loaders);
    }
}
