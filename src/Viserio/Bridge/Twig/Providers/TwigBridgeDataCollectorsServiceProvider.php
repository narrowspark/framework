<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Twig_Environment as TwigEnvironment;
use Twig_Extension_Profiler;
use Twig_Profiler_Profile;
use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class TwigBridgeDataCollectorsServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
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
            Twig_Profiler_Profile::class => [self::class, 'createTwigProfilerProfile'],
            TwigEnvironment::class       => [self::class, 'createTwigEnvironment'],
            ProfilerContract::class      => [self::class, 'createProfiler'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'profiler'];
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

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\Profiler\Profiler
     */
    public static function createProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        if ($getPrevious !== null) {
            self::resolveOptions($container);

            $profiler = $getPrevious();

            if (self::$options['collector']['twig'] === true) {
                $profiler->addCollector(new TwigDataCollector(
                    $container->get(Twig_Profiler_Profile::class),
                    $container->get(TwigEnvironment::class)
                ));
            }

            return $profiler;
        }

        return null;
    }

    /**
     * Create a new Twig_Profiler_Profile instance.
     *
     * @return \Twig_Profiler_Profile
     */
    public static function createTwigProfilerProfile(): Twig_Profiler_Profile
    {
        return new Twig_Profiler_Profile();
    }

    /**
     * Wrap Twig_Environment.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Twig_Environment
     */
    public static function createTwigEnvironment(ContainerInterface $container, ?callable $getPrevious = null): ?TwigEnvironment
    {
        if ($getPrevious !== null) {
            self::resolveOptions($container);

            $twig = $getPrevious();

            if (self::$options['collector']['twig'] === true) {
                $twig->addExtension(new Twig_Extension_Profiler(
                    $container->get(Twig_Profiler_Profile::class)
                ));
            }

            return $twig;
        }

        return null;
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
