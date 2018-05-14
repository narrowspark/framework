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

use Viserio\Component\Config\Command\ConfigCacheCommand;
use Viserio\Component\Config\Command\ConfigClearCommand;
use Viserio\Component\Config\Container\Pipeline\ResolveOptionDefinitionPipe;
use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Config\Repository;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
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
        $count = 0;

        foreach ($this->containerBuilder->getPipelineConfig()->getBeforeOptimizationPipelines() as $pipeline) {
            if ($pipeline instanceof ResolveOptionDefinitionPipe) {
                $count++;
            }
        }

        self::assertSame(1, $count);

        $config = $this->container->get(RepositoryContract::class);
        $config->set('factory_test', 'bar');

        $alias = $this->container->get('config');

        self::assertInstanceOf(Repository::class, $this->container->get(RepositoryContract::class));
        self::assertInstanceOf(Repository::class, $this->container->get(Repository::class));
        self::assertEquals($config, $alias);
        self::assertTrue($config->has('factory_test'));
        self::assertSame('bar', $config->get('factory_test'));

        self::assertInstanceOf(ConfigCacheCommand::class, $this->container->get(ConfigCacheCommand::class));
        self::assertInstanceOf(ConfigClearCommand::class, $this->container->get(ConfigClearCommand::class));

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(ConfigCacheCommand::getDefaultName()));
        self::assertTrue($console->has(ConfigClearCommand::getDefaultName()));
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
