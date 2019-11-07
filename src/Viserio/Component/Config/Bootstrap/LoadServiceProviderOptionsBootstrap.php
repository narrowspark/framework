<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Config\Bootstrap;

use Narrowspark\Arr\Arr;
use ReflectionClass;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProviderBootstrap;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

final class LoadServiceProviderOptionsBootstrap implements BootstrapStateContract
{
    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 32;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return BootstrapStateContract::TYPE_AFTER;
    }

    /**
     * {@inheritdoc}
     */
    public static function getBootstrapper(): string
    {
        return LoadServiceProviderBootstrap::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported(KernelContract $kernel): bool
    {
        return ! $kernel->isBootstrapped();
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $containerBuilder = $kernel->getContainerBuilder();

        $providerConfigs = [];

        foreach ($kernel->getRegisteredServiceProviders() as $provider) {
            $reflection = new ReflectionClass($provider);

            if ($reflection->implementsInterface(ProvidesDefaultOptionContract::class) && \count($defaultOption = $provider::getDefaultOptions()) !== 0) {
                $providerConfigs[] = [
                    'dimensions' => $reflection->implementsInterface(RequiresComponentConfigContract::class) ? $provider::getDimensions() : [],
                    'options' => $defaultOption,
                ];
            }
        }

        $preparedConfig = [];

        foreach ($providerConfigs as $configs) {
            $dimensions = $configs['dimensions'];

            $dimensionsCount = \count($dimensions);
            $config = $configs['options'];

            for ($i = $dimensionsCount - 1; $i >= 0; $i--) {
                $config = [$dimensions[$i] => $config];
            }

            $preparedConfig = Arr::merge($preparedConfig, $config);
        }

        $containerBuilder->findDefinition(RepositoryContract::class)
            ->addMethodCall('setArray', [$preparedConfig]);
    }
}
