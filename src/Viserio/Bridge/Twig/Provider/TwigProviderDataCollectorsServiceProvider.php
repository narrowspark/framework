<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;
use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class TwigProviderDataCollectorsServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use StaticOptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Profile::class => function (): Profile {
                return new Profile();
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
    public function getDefaultOptions(): iterable
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
     * @return null|\Viserio\Component\Contracts\Profiler\Profiler
     */
    public static function createProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        $profiler = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($getPrevious !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['twig'] === true) {
                $profiler->addCollector(new TwigDataCollector(
                    $container->get(Profile::class),
                    $container->get(TwigEnvironment::class)
                ));
            }

            return $profiler;
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
    public static function createTwigEnvironment(ContainerInterface $container, ?callable $getPrevious = null): ?TwigEnvironment
    {
        $twig = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($twig !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['twig'] === true) {
                $twig->addExtension(new ProfilerExtension(
                    $container->get(Profile::class)
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
