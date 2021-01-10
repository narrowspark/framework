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

namespace Viserio\Component\Config\Tests\Integration\Container\Pipeline\Integration;

use stdClass;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigConfiguration;
use Viserio\Component\Container\Test\AbstractContainerTestCase;

/**
 * @internal
 *
 * @covers \Viserio\Component\Config\Container\Pipeline\ResolveConfigDefinitionPipe
 *
 * @small
 */
final class ResolveConfigDefinitionPipeTest extends AbstractContainerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const DUMP_CLASS_CONTAINER = false;

    /**
     * {@inheritdoc}
     */
    protected const SKIP_TEST_PIPE = true;

    public function testSimpleConfigDefinition(): void
    {
        $this->containerBuilder->register(new ConfigServiceProvider());
        $this->containerBuilder->setParameter('doctrine', [
            'connection' => [],
        ]);
        $this->containerBuilder->singleton('foo', stdClass::class)
            ->addArgument(new ConfigDefinition(ConnectionComponentDefaultConfigConfiguration::class))
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);
        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testConfigDefinitionWithNotFoundParameters(): void
    {
        $this->containerBuilder->register(new ConfigServiceProvider());
        $this->containerBuilder->setParameter('test', [
            'connection' => [],
        ]);
        $this->containerBuilder->singleton('foo', stdClass::class)
            ->addArgument(new ConfigDefinition(ConnectionComponentDefaultConfigConfiguration::class))
            ->setPublic(true);

        $this->containerBuilder->compile();

        $logs = $this->containerBuilder->getLogs();

        self::assertSame('Viserio\\Component\\Config\\Container\\Pipeline\\ResolveConfigDefinitionPipe: Using the first key [doctrine] of the config dimensions failed to get parameter for [foo].', $logs[0]);

        $this->dumpContainer(__FUNCTION__);
        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testConfigDefinitionWithNotDefaultConfig(): void
    {
        $this->containerBuilder->register(new ConfigServiceProvider());
        $this->containerBuilder->setParameter('doctrine', [
            'connection' => [],
        ]);
        $this->containerBuilder->singleton('foo', stdClass::class)
            ->addArgument(new ConfigDefinition(ConnectionComponentConfiguration::class))
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);
        $this->assertDumpedContainer(__FUNCTION__);
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
