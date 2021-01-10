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

namespace Viserio\Component\Container\Tests\Integration;

use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Dumper\PhpDumper;
use Viserio\Component\Container\PhpParser\PrettyPrinter;

/**
 * @internal
 */
abstract class BaseContainerTest extends TestCase
{
    /** @var string */
    protected const COMPILATION_DIR = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Compiled' . \DIRECTORY_SEPARATOR;

    /** @var \Viserio\Component\Container\ContainerBuilder */
    protected $containerBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     */
    protected function assertDumpedContainer(string $className): void
    {
        $dumper = new PhpDumper(
            $this->containerBuilder,
            (new ParserFactory())->create(
                ParserFactory::PREFER_PHP7,
                new Emulative([
                    'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
                ])
            ),
            new PrettyPrinter()
        );

        self::assertStringEqualsFile(
            self::COMPILATION_DIR . $className . '.php',
            $dumper->dump(['class' => $className])
        );
    }
}
