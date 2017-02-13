<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Twig_Extension_Profiler;
use Twig_Profiler_Profile;
use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;
use Viserio\Bridge\Twig\TwigEnvironment;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class TwigBridgeDataCollectorsServiceProvider implements ServiceProvider, RequiresComponentConfigContract, RequiresMandatoryOptionsContract
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
            Twig_Profiler_Profile::class => [self::class, 'createTwigProfilerProfile'],
            TwigEnvironment::class       => [self::class, 'createTwigEnvironment'],
            WebProfilerContract::class   => [self::class, 'createWebProfiler'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'webprofiler'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'collector' => [
                'twig',
            ],
        ];
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        self::resolveOptions($container);

        $profiler = $container->get(WebProfilerContract::class);

        if (self::$options['collector']['twig'] !== false) {
            $profiler->addCollector(new TwigDataCollector(
                $container->get(Twig_Profiler_Profile::class)
            ));
        }

        return $profiler;
    }

    public static function createTwigProfilerProfile(): Twig_Profiler_Profile
    {
        return new Twig_Profiler_Profile();
    }

    public static function createTwigEnvironment(ContainerInterface $container): TwigEnvironment
    {
        self::resolveOptions($container);

        $twig = $container->get(TwigEnvironment::class);

        if (self::$options['collector']['twig'] !== false) {
            $twig->addExtension(new Twig_Extension_Profiler(
                $container->get(Twig_Profiler_Profile::class)
            ));
        }

        return $twig;
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
