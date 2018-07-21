<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Lexer;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\View\Factory as FactoryContract;
use Viserio\Component\Contract\View\Finder as FinderContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\View\Engine\EngineResolver;
use Viserio\Provider\Twig\Engine\TwigEngine;
use Viserio\Provider\Twig\Loader as TwigLoader;

class TwigServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            LoaderInterface::class => [self::class, 'createTwigLoader'],
            TwigLoader::class      => function (ContainerInterface $container) {
                return $container->get(LoaderInterface::class);
            },
            TwigEnvironment::class => [self::class, 'createTwigEnvironment'],
            TwigEngine::class      => [self::class, 'createTwigEngine'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            FactoryContract::class => [self::class, 'extendViewFactory'],
            EngineResolver::class  => [self::class, 'extendEngineResolver'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
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

    /**
     * Create a new twig engine instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Provider\Twig\Engine\TwigEngine
     */
    public static function createTwigEngine(ContainerInterface $container): TwigEngine
    {
        $engine = new TwigEngine($container->get(TwigEnvironment::class), $container->get('config'));
        $engine->setContainer($container);

        return $engine;
    }

    /**
     * Extend ViewFactory.
     *
     * @param \Psr\Container\ContainerInterface             $container
     * @param null|\Viserio\Component\Contract\View\Factory $view
     *
     * @return null|\Viserio\Component\Contract\View\Factory
     */
    public static function extendViewFactory(
        ContainerInterface $container,
        ?FactoryContract $view = null
    ): ?FactoryContract {
        if ($view !== null) {
            // @var FactoryContract $view
            $view->addExtension('twig', 'twig');
        }

        return $view;
    }

    /**
     * Extend EngineResolver with twig extension.
     *
     * @param \Psr\Container\ContainerInterface                  $container
     * @param null|\Viserio\Component\View\Engine\EngineResolver $engines
     *
     * @return null|\Viserio\Component\View\Engine\EngineResolver
     */
    public static function extendEngineResolver(
        ContainerInterface $container,
        ?EngineResolver $engines = null
    ): ?EngineResolver {
        if ($engines !== null) {
            // @var EngineResolver $engines
            $engines->register('twig', function () use ($container) {
                return $container->get(TwigEngine::class);
            });
        }

        return $engines;
    }

    /**
     * Create a twig environment.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Twig\Environment
     */
    public static function createTwigEnvironment(ContainerInterface $container): TwigEnvironment
    {
        $options     = self::resolveOptions($container->get('config'));
        $twigOptions = $options['engines']['twig']['options'];

        $twig = new TwigEnvironment(
            $container->get(LoaderInterface::class),
            $twigOptions
        );

        if ($container->has(Lexer::class)) {
            $twig->setLexer($container->get(Lexer::class));
        }

        return $twig;
    }

    /**
     * Create a twig bridge loader.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Twig\Loader\LoaderInterface
     */
    public static function createTwigLoader(ContainerInterface $container): LoaderInterface
    {
        $options = self::resolveOptions($container->get('config'));

        $loaders     = [];
        $twigOptions = $options['engines']['twig'];
        $loader      = new TwigLoader($container->get(FinderContract::class));

        if (isset($twigOptions['file_extension'])) {
            $loader->setExtension($twigOptions['file_extension']);
        }

        $loaders[] = $loader;

        if (isset($twigOptions['templates']) && \is_array($twigOptions['templates'])) {
            $loaders[] = new ArrayLoader($twigOptions['templates']);
        }

        if (isset($twigOptions['loaders']) && \is_array($twigOptions['loaders'])) {
            $loaders = \array_merge($loaders, $twigOptions['loaders']);
        }

        return new ChainLoader($loaders);
    }
}
