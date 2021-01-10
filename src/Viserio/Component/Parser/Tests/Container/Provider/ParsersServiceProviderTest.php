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

namespace Viserio\Component\Parser\Tests\Container\Provider;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Parser\Container\Provider\ParserServiceProvider;
use Viserio\Component\Parser\Dumper;
use Viserio\Component\Parser\FileLoader;
use Viserio\Component\Parser\GroupParser;
use Viserio\Component\Parser\Parser;
use Viserio\Component\Parser\TaggableParser;
use Viserio\Contract\Parser\Loader as LoaderContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ParsersServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(FileLoader::class, $this->container->get(FileLoader::class));
        self::assertInstanceOf(FileLoader::class, $this->container->get(LoaderContract::class));
        self::assertInstanceOf(TaggableParser::class, $this->container->get(TaggableParser::class));
        self::assertInstanceOf(GroupParser::class, $this->container->get(GroupParser::class));
        self::assertInstanceOf(Parser::class, $this->container->get(Parser::class));
        self::assertInstanceOf(Dumper::class, $this->container->get(Dumper::class));
        self::assertInstanceOf(Parser::class, $this->container->get('parser'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new ParserServiceProvider());
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
