<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
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
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\Support\Str;
use Viserio\Component\View\Engines\EngineResolver;

class TwigBridgeServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
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
            Twig_LoaderInterface::class           => [self::class, 'createTwigLoader'],
            TwigLoader::class                     => function (ContainerInterface $container) {
                return $container->get(Twig_LoaderInterface::class);
            },
            TwigEnvironment::class      => [self::class, 'createTwigEnvironment'],
            FactoryContract::class      => [self::class, 'createViewFactory'],
            EngineResolver::class       => [self::class, 'createEngineResolver'],
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

    public static function createTwigEngine(ContainerInterface $container): TwigEngine
    {
        return new TwigEngine($container->get(TwigEnvironment::class), $container);
    }

    public static function createViewFactory(ContainerInterface $container, ?callable $getPrevious = null): ?FactoryContract
    {
        if ($getPrevious !== null) {
            $view = $getPrevious();

            $view->addExtension('twig', 'twig');

            return $view;
        }

        return null;
    }

    public static function createEngineResolver(ContainerInterface $container, ?callable $getPrevious = null): ?EngineResolver
    {
        if ($getPrevious !== null) {
            $engines = $getPrevious();

            $engines->register('twig', function () use ($container) {
                return $container->get(TwigEngine::class);
            });

            return $engines;
        }

        return null;
    }

    public static function createTwigEnvironment(ContainerInterface $container): TwigEnvironment
    {
        self::resolveOptions($container);

        $options = self::$options['engines']['twig']['options'];

        $twig = new TwigEnvironment(
            $container->get(Twig_LoaderInterface::class),
            $options
        );

        if ($container->has(Twig_Lexer::class)) {
            $twig->setLexer($container->get(Twig_Lexer::class));
        }

        if ($options['debug']) {
            $twig->addExtension(new DumpExtension());
        }

        self::registerViserioTwigExtension($twig, $container);

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

        $loaders = [];
        $options = self::$options['engines']['twig'];
        $loader  = new TwigLoader(
            $container->get(FinderContract::class)
        );

        if (isset($options['file_extension'])) {
            $loader->setExtension($options['file_extension']);
        }

        $loaders[] = $loader;

        if (isset($options['templates']) && is_array($options['templates'])) {
            $loaders[] = new Twig_Loader_Array($options['templates']);
        }

        if (isset($options['loaders']) && is_array($options['loaders'])) {
            $loaders = array_merge($loaders, $options['loaders']);
        }

        return new Twig_Loader_Chain($loaders);
    }

    /**
     * Register viserio twig extension.
     *
     * @param \Twig_Environment                     $twig
     * @param \Interop\Container\ContainerInterface $container
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
