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

namespace Viserio\Component\Config\Tests\Container\Provider;

use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Config\Repository;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Parser\FileLoader;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Parser\Loader as LoaderContract;

/**
 * @internal
 *
 * @small
 */
final class ConfigServiceProviderTest extends AbstractContainerTestCase
{
    public function testBuild(): void
    {
        $config = $this->container->get(RepositoryContract::class);
        $config->set('factory_test', 'bar');

        $alias = $this->container->get('config');

        self::assertInstanceOf(Repository::class, $this->container->get(RepositoryContract::class));
        self::assertInstanceOf(Repository::class, $this->container->get(Repository::class));
        self::assertEquals($config, $alias);
        self::assertTrue($config->has('factory_test'));
        self::assertSame('bar', $config->get('factory_test'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->bind(LoaderContract::class, new FileLoader());
        $containerBuilder->register(new ConfigServiceProvider());
        $containerBuilder->register(new ConsoleServiceProvider());
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
