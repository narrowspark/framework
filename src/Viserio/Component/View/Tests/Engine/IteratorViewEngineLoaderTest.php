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

namespace Viserio\Component\View\Tests\Engines;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\RewindableGenerator;
use Viserio\Component\View\Engine\FileEngine;
use Viserio\Component\View\Engine\IteratorViewEngineLoader;
use Viserio\Component\View\Engine\PhpEngine;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class IteratorViewEngineLoaderTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Container\RewindableGenerator */
    private $iterator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->iterator = new RewindableGenerator(function () {
            yield 'file' => new FileEngine();

            yield 'php' => new PhpEngine();
        }, 2);
    }

    public function testHas(): void
    {
        $commandLoader = new IteratorViewEngineLoader($this->iterator);

        self::assertTrue($commandLoader->has('file'));
        self::assertTrue($commandLoader->has('php'));
        self::assertFalse($commandLoader->has('baz'));
    }

    public function testGet(): void
    {
        $commandLoader = new IteratorViewEngineLoader($this->iterator);

        self::assertInstanceOf(FileEngine::class, $commandLoader->get('file'));
    }

    public function testGetCommandNames(): void
    {
        $commandLoader = new IteratorViewEngineLoader($this->iterator);

        self::assertSame(['file', 'php'], $commandLoader->getNames());
    }
}
