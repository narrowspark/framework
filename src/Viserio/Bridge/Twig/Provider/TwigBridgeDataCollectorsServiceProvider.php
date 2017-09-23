<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Provider;

use Interop\Container\ServiceProviderInterface;
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
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            Profile::class => function (): Profile {
                return new Profile();
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            TwigEnvironment::class  => [self::class, 'extendTwigEnvironment'],
            ProfilerContract::class => [self::class, 'extendProfiler'],
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
     * @param \Psr\Container\ContainerInterface                  $container
     * @param null|\Viserio\Component\Contract\Profiler\Profiler $profiler
     *
     * @return null|\Viserio\Component\Contract\Profiler\Profiler
     */
    public static function extendProfiler(ContainerInterface $container, ?ProfilerContract $profiler = null): ?ProfilerContract
    {
        if ($profiler !== null) {
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
     * @param null|\Twig\Environment            $twig
     *
     * @return null|\Twig\Environment
     */
    public static function extendTwigEnvironment(ContainerInterface $container, ?TwigEnvironment $twig = null): ?TwigEnvironment
    {
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
