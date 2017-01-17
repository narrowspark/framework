<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\Translation\DataCollectors\ViserioTranslationDataCollector;

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
