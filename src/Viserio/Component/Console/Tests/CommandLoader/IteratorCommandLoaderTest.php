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

namespace Viserio\Component\Console\Tests\Command;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\CommandLoader\IteratorCommandLoader;
use Viserio\Component\Console\Tests\Fixture\GoodbyeCommand;
use Viserio\Component\Console\Tests\Fixture\GreetCommand;
use Viserio\Component\Container\RewindableGenerator;

/**
 * @internal
 *
 * @small
 */
final class IteratorCommandLoaderTest extends TestCase
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
            yield GoodbyeCommand::getDefaultName() => new GoodbyeCommand();

            yield 'greet' => new GreetCommand();
        }, 2);
    }

    public function testHas(): void
    {
        $commandLoader = new IteratorCommandLoader($this->iterator);

        self::assertTrue($commandLoader->has(GoodbyeCommand::getDefaultName()));
        self::assertTrue($commandLoader->has('greet'));
        self::assertFalse($commandLoader->has('baz'));
    }

    public function testGet(): void
    {
        $commandLoader = new IteratorCommandLoader($this->iterator);

        self::assertInstanceOf(GoodbyeCommand::class, $commandLoader->get(GoodbyeCommand::getDefaultName()));
    }

    public function testGetCommandNames(): void
    {
        $commandLoader = new IteratorCommandLoader($this->iterator);

        self::assertSame(['goodbye', 'greet'], $commandLoader->getNames());
    }
}
