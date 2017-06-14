<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Foundation\DataCollector\FilesLoadedCollector;
use Viserio\Component\Foundation\DataCollector\NarrowsparkDataCollector;
use Viserio\Component\Foundation\DataCollector\ViserioHttpDataCollector;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class FoundationDataCollectorServiceProvider implements
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
            ProfilerContract::class => [self::class, 'extendProfiler'],
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
                'narrowspark'  => false,
                'files'        => false,
                'viserio_http' => false,
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
    public static function extendProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        $profiler = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($profiler !== null) {
            $options = self::resolveOptions($container);
            $kernel  = $container->get(KernelContract::class);

            if ($options['collector']['narrowspark']) {
                $profiler->addCollector(new NarrowsparkDataCollector(), -100);
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
                $profiler->addCollector(new FilesLoadedCollector($kernel->getProjectDir()));
            }

            return $profiler;
        }

        return $profiler;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
