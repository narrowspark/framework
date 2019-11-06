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

namespace Viserio\Component\Container\Tests\IntegrationTest;

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
     * @param string $className
     *
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return void
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
