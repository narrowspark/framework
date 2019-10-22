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

namespace Viserio\Component\Cron\Container\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Cron\Command\CronListCommand;
use Viserio\Component\Cron\Command\ScheduleRunCommand;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Cron\Schedule as ScheduleContract;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

class CronServiceProvider implements AliasServiceProviderContract,
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract,
    RequiresValidatedConfigContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(ScheduleContract::class, Schedule::class)
            ->setArguments([
                new OptionDefinition('path', self::class),
                new OptionDefinition('console', self::class),
            ])
            ->setMethodCalls([
                ['setCacheItemPool', [new ReferenceDefinition(CacheItemPoolInterface::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)]],
                ['setContainer'],
            ]);

        $container->singleton(CronListCommand::class)
            ->addTag('console.command');
        $container->singleton(ScheduleRunCommand::class)
            ->addTag('console.command');
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            Schedule::class => ScheduleContract::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'cron'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return ['path'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'console' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'path' => ['string'],
            'console' => ['string', 'null'],
        ];
    }
}
