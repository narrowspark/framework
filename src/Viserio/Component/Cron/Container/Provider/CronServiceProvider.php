<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Cron\Container\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Cron\Command\CronListCommand;
use Viserio\Component\Cron\Command\ScheduleRunCommand;
use Viserio\Component\Cron\Schedule;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Cron\Schedule as ScheduleContract;

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
    public static function getDimensions(): iterable
    {
        return ['viserio', 'cron'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return [
            'path',
            'env',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'console' => null,
            'maintenance' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getConfigValidators(): iterable
    {
        return [
            'env' => ['string'],
            'maintenance' => ['bool'],
            'path' => ['string'],
            'console' => ['string', 'null'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(ScheduleContract::class, Schedule::class)
            ->setArguments([
                (new ConfigDefinition(self::class))
                    ->setKey('path'),
                (new ConfigDefinition(self::class))
                    ->setKey('console'),
            ])
            ->setMethodCalls([
                ['setCacheItemPool', [new ReferenceDefinition(CacheItemPoolInterface::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)]],
                ['setContainer'],
            ]);

        $container->singleton(CronListCommand::class)
            ->addTag(AddConsoleCommandPipe::TAG);
        $container->singleton(ScheduleRunCommand::class)
            ->setArguments([
                (new ConfigDefinition(self::class))
                    ->setKey('env'),
                (new ConfigDefinition(self::class))
                    ->setKey('maintenance'),
            ])
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
}
