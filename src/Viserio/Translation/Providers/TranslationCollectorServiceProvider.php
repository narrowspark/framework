<?php
declare(strict_types=1);
namespace Viserio\Translation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Translation\DataCollectors\ViserioTranslationDataCollector;

class TranslationCollectorServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'webprofiler.translation';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ViserioTranslationDataCollector::class => [self::class, 'createViserioTranslationDataCollector'],
        ];
    }

    public static function createViserioTranslationDataCollector(
        ContainerInterface $container
    ): ViserioTranslationDataCollector {
        return new ViserioTranslationDataCollector(
            $container->get(TranslatorContract::class)
        );
    }
}
