<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Twig_Environment as TwigEnvironment;
use Twig_Lexer;
use Twig_Loader_Array;
use Twig_Loader_Chain;
use Twig_LoaderInterface;
use Viserio\Bridge\Twig\Engine\TwigEngine;
use Viserio\Bridge\Twig\Extensions\ConfigExtension;
use Viserio\Bridge\Twig\Extensions\DumpExtension;
use Viserio\Bridge\Twig\Extensions\SessionExtension;
use Viserio\Bridge\Twig\Extensions\StrExtension;
use Viserio\Bridge\Twig\Extensions\TranslatorExtension;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;
use Viserio\Component\Support\Str;
use Viserio\Component\View\Engines\EngineResolver;

class TwigBridgeServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use StaticOptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Twig_LoaderInterface::class => [self::class, 'createTwigLoader'],
            TwigLoader::class           => function (ContainerInterface $container) {
                return $container->get(Twig_LoaderInterface::class);
            },
            TwigEnvironment::class      => [self::class, 'createTwigEnvironment'],
            FactoryContract::class      => [self::class, 'extendViewFactory'],
            EngineResolver::class       => [self::class, 'extendEngineResolver'],
            TwigEngine::class           => [self::class, 'createTwigEngine'],
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

    /**
     * Create a new twig engine instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Bridge\Twig\Engine\TwigEngine
     */
    public static function createTwigEngine(ContainerInterface $container): TwigEngine
    {
        return new TwigEngine($container->get(TwigEnvironment::class), $container);
    }

    /**
     * Extend ViewFactory.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\View\Factory
     */
    public static function extendViewFactory(ContainerInterface $container, ?callable $getPrevious = null): ?FactoryContract
    {
        $view = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($view !== null) {
            $view->addExtension('twig', 'twig');

            return $view;
        }

        return $view;
    }

    /**
     * Extend EngineResolver with twig extension.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\View\Factory
     */
    public static function extendEngineResolver(ContainerInterface $container, ?callable $getPrevious = null): ?EngineResolver
    {
        $engines = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($engines !== null) {
            $engines->register('twig', function () use ($container) {
                return $container->get(TwigEngine::class);
            });

            return $engines;
        }

        return $engines;
    }

    /**
     * Create a twig environment.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Twig_Environment
     */
    public static function createTwigEnvironment(ContainerInterface $container): TwigEnvironment
    {
        $options     = self::resolveOptions($container);
        $twigOptions = $options['engines']['twig']['options'];

        $twig = new TwigEnvironment(
            $container->get(Twig_LoaderInterface::class),
            $twigOptions
        );

        if ($container->has(Twig_Lexer::class)) {
            $twig->setLexer($container->get(Twig_Lexer::class));
        }

        if ($twigOptions['debug'] && class_exists(VarCloner::class)) {
            $twig->addExtension(new DumpExtension());
        }

        self::registerViserioTwigExtension($twig, $container);

        return $twig;
    }

    /**
     * Create a twig bridge loader.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Twig_LoaderInterface
     */
    public static function createTwigLoader(ContainerInterface $container): Twig_LoaderInterface
    {
        $options = self::resolveOptions($container);

        $loaders     = [];
        $twigOptions = $options['engines']['twig'];
        $loader      = new TwigLoader(
            $container->get(FinderContract::class)
        );

        if (isset($twigOptions['file_extension'])) {
            $loader->setExtension($twigOptions['file_extension']);
        }

        $loaders[] = $loader;

        if (isset($twigOptions['templates']) && is_array($twigOptions['templates'])) {
            $loaders[] = new Twig_Loader_Array($twigOptions['templates']);
        }

        if (isset($twigOptions['loaders']) && is_array($twigOptions['loaders'])) {
            $loaders = array_merge($loaders, $twigOptions['loaders']);
        }

        return new Twig_Loader_Chain($loaders);
    }

    /**
     * Register viserio twig extension.
     *
     * @param \Twig_Environment                 $twig
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    protected static function registerViserioTwigExtension(TwigEnvironment $twig, ContainerInterface $container): void
    {
        if ($container->has(TranslatorContract::class)) {
            $twig->addExtension(new TranslatorExtension($container->get(TranslatorContract::class)));
        }

        if (class_exists(Str::class)) {
            $twig->addExtension(new StrExtension());
        }

        if ($container->has(StoreContract::class)) {
            $twig->addExtension(new SessionExtension($container->get(StoreContract::class)));
        }

        if ($container->has(RepositoryContract::class)) {
            $twig->addExtension(new ConfigExtension($container->get(RepositoryContract::class)));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
