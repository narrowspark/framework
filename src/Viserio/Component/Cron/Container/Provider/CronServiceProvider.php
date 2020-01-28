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
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Cron\Command\CronListCommand;
use Viserio\Component\Cron\Command\ScheduleRunCommand;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Cron\Schedule as ScheduleContract;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;

class CronServiceProvider implements AliasServiceProviderContract,
    ProvidesDefaultConfigContract,
    RequiresComponentConfigContract,
    RequiresMandatoryConfigContract,
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
            ->addTag(AddConsoleCommandPipe::TAG);
        $container->singleton(ScheduleRunCommand::class)
            ->addTag(AddConsoleCommandPipe::TAG);
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
    public static function getDimensions(): iterable
    {
        return ['viserio', 'cron'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['path'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'console' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getConfigValidators(): iterable
    {
        return [
            'path' => ['string'],
            'console' => ['string', 'null'],
        ];
    }
}
