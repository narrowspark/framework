<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Twig_Environment as TwigEnvironment;
use Twig_Extension_Profiler;
use Twig_Profiler_Profile;
use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class TwigBridgeDataCollectorsServiceProvider implements
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
            Twig_Profiler_Profile::class => function (): Twig_Profiler_Profile {
                return new Twig_Profiler_Profile();
            },
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
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\Profiler\Profiler
     */
    public static function createProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        $profiler = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($getPrevious !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['twig'] === true) {
                $profiler->addCollector(new TwigDataCollector(
                    $container->get(Twig_Profiler_Profile::class),
                    $container->get(TwigEnvironment::class)
                ));
            }

            return $profiler;
        }

        return $profiler;
    }

    /**
     * Wrap Twig_Environment.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Twig_Environment
     */
    public static function createTwigEnvironment(ContainerInterface $container, ?callable $getPrevious = null): ?TwigEnvironment
    {
        $twig = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($twig !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['twig'] === true) {
                $twig->addExtension(new Twig_Extension_Profiler(
                    $container->get(Twig_Profiler_Profile::class)
                ));
            }

            return $twig;
        }

        return $twig;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
