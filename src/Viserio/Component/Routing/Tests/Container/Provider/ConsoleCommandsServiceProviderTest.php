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

namespace Viserio\Component\Routing\Tests\Provider;

use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Routing\Command\RouteListCommand;
use Viserio\Component\Routing\Container\Provider\ConsoleCommandsServiceProvider;
use Viserio\Component\Routing\Container\Provider\RoutingServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class ConsoleCommandsServiceProviderTest extends AbstractContainerTestCase
{
    public function testGetServices(): void
    {
        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(RouteListCommand::getDefaultName()));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('viserio', [
            'console' => [
                'name' => 'test',
                'version' => '1',
            ],
        ]);
        $containerBuilder->register(new RoutingServiceProvider());
        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->register(new ConsoleCommandsServiceProvider());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
