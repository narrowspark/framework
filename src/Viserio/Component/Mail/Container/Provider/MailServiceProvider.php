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

namespace Viserio\Component\Mail\Container\Provider;

use Psr\Log\LoggerInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Mail\MailManager;
use Viserio\Component\Mail\TransportFactory;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Mail\Mailer as MailerContract;
use Viserio\Contract\Mail\QueueMailer as QueueMailerContract;
use Viserio\Contract\View\Factory as ViewFactoryContract;

class MailServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(TransportFactory::class)
            ->addMethodCall('setLogger', [new ReferenceDefinition(LoggerInterface::class)]);

        $container->singleton(MailManager::class)
            ->setArguments([new ReferenceDefinition('config'), new ReferenceDefinition(TransportFactory::class)])
            ->addMethodCall('setContainer')
            ->addMethodCall('setViewFactory', [new ReferenceDefinition(ViewFactoryContract::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('setEventManager', [new ReferenceDefinition(EventManagerContract::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)]);

        $container->singleton(MailerContract::class, [new ReferenceDefinition(MailManager::class), 'getConnection']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            QueueMailerContract::class => MailerContract::class,
            'mailer' => MailerContract::class,
        ];
    }
}
