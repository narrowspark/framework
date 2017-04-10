<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\Translation\DataCollectors\ViserioTranslationDataCollector;

class TranslationDataCollectorServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
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
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\Profiler\Profiler
     */
    public static function createProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        $profiler = $getPrevious();

        if ($profiler !== null) {
            self::resolveOptions($container);

            if (self::$options['collector']['translation']) {
                $profiler->addCollector(new ViserioTranslationDataCollector(
                    $container->get(TranslatorContract::class)
                ));
            }

            return $profiler;
        }

        return $profiler;
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
