<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;
use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class TwigBridgeDataCollectorsServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Profile::class => function (): Profile {
                return new Profile();
            },
            TwigEnvironment::class       => [self::class, 'extendTwigEnvironment'],
            ProfilerContract::class      => [self::class, 'createProfiler'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'profiler'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'collector' => [
                'twig' => false,
            ],
        ];
    }

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Contract\Profiler\Profiler
     */
    public static function createProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        $profiler = \is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($getPrevious !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['twig'] === true) {
                $profiler->addCollector(new TwigDataCollector(
                    $container->get(Profile::class),
                    $container->get(TwigEnvironment::class)
                ));
            }
        }

        return $profiler;
    }

    /**
     * Wrap Twig Environment.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Twig\Environment
     */
    public static function extendTwigEnvironment(ContainerInterface $container, ?callable $getPrevious = null): ?TwigEnvironment
    {
        $twig = \is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($twig !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['twig'] === true) {
                $twig->addExtension(new ProfilerExtension(
                    $container->get(Profile::class)
                ));
            }
        }

        return $twig;
    }
}
