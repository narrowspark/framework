<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Foundation\DataCollector\FilesLoadedCollector;
use Viserio\Component\Foundation\DataCollector\NarrowsparkDataCollector;
use Viserio\Component\Foundation\DataCollector\ViserioHttpDataCollector;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class FoundationDataCollectorServiceProvider implements
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
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
                'narrowspark'  => false,
                'files'        => false,
                'viserio_http' => false,
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
            $kernel  = $container->get(KernelContract::class);

            if ($options['collector']['narrowspark']) {
                $profiler->addCollector(new NarrowsparkDataCollector($kernel->getEnvironment(), $kernel->isDebug()), -100);
            }

            if ($options['collector']['viserio_http']) {
                $profiler->addCollector(
                    new ViserioHttpDataCollector(
                        $container->get(RouterContract::class),
                        $kernel->getRoutesPath()
                    ),
                    1
                );
            }

            if ($options['collector']['files']) {
                $profiler->addCollector(new FilesLoadedCollector($kernel->getRootDir()));
            }
        }

        return $profiler;
    }
}
