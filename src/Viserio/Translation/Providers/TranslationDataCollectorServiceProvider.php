<?php
declare(strict_types=1);
namespace Viserio\Translation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Translation\DataCollectors\ViserioTranslationDataCollector;

class TranslationDataCollectorServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            WebProfilerContract::class => [self::class, 'createWebProfiler'],
        ];
    }

    public static function createWebProfiler(
        ContainerInterface $container
    ): WebProfilerContract {
        $profiler = $container->get(WebProfilerContract::class);

        if (self::getConfig($container, 'collector.translation', false)) {
            $profiler->addCollector(new ViserioTranslationDataCollector(
                $container->get(TranslatorContract::class)
            ));
        }

        return $profiler;
    }
}
