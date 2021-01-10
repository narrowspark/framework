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

namespace Viserio\Component\Exception\Container\Provider;

use Psr\Log\LoggerInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Exception\Console\Handler;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;

class ConsoleExceptionServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(ConsoleHandlerContract::class, Handler::class)
            ->setArguments([
                new ReferenceDefinition('config'),
                new ReferenceDefinition(LoggerInterface::class, ReferenceDefinition::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            Handler::class => ConsoleHandlerContract::class,
        ];
    }
}
