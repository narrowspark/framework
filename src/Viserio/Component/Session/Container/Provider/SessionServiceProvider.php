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

namespace Viserio\Component\Session\Container\Provider;

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Session\Handler\CookieSessionHandler;
use Viserio\Component\Session\SessionManager;
use Viserio\Contract\Cache\Manager as CacheManagerContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Contract\Events\Event as EventContract;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\HttpFoundation\Terminable as TerminableContract;
use Viserio\Contract\Session\Store as StoreContract;

class SessionServiceProvider implements AliasServiceProviderContract, ExtendServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(SessionManager::class)
            ->addArgument(new ReferenceDefinition('config'))
            ->addMethodCall('setCacheManager', [new ReferenceDefinition(CacheManagerContract::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('setCookieJar', [new ReferenceDefinition(JarContract::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)]);

        $container->singleton(StoreContract::class, [new ReferenceDefinition(SessionManager::class), 'getDriver']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            'session' => SessionManager::class,
            'session.store' => StoreContract::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            EventManagerContract::class => static function (ObjectDefinitionContract $definition): void {
                $definition->addMethodCall('attach', [TerminableContract::TERMINATE, static function (EventContract $event): void {
                    if (($target = $event->getTarget()) !== null) {
                        /** @var StoreContract $driver */
                        $driver = $target->getContainer()->get(SessionManager::class)->getDriver();

                        if (! $driver->getHandler() instanceof CookieSessionHandler) {
                            $driver->save();
                        }
                    }
                }]);
            },
        ];
    }
}
