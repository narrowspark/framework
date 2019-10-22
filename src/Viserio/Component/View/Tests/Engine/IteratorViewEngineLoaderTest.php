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
