<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;
use Viserio\Component\Translation\DataCollectors\ViserioTranslationDataCollector;

class TranslationDataCollectorServiceProvider implements
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
            ProfilerContract::class => [self::class, 'createProfiler'],
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
                'translation' => false,
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

        if ($profiler !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['translation']) {
                $profiler->addCollector(new ViserioTranslationDataCollector(
                    $container->get(TranslatorContract::class)
                ));
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
