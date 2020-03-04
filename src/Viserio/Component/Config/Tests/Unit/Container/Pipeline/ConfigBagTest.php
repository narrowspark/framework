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

namespace Viserio\Component\Config\Tests\Unit\Unit\Container\Pipeline;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Container\Pipeline\ConfigBag;
use Viserio\Contract\Config\Exception\LogicException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Config\Container\Pipeline\ConfigBag
 *
 * @small
 */
final class ConfigBagTest extends TestCase
{
    private ConfigBag $configBag;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configBag = new ConfigBag(['test' => 'foo'], ['bar' => 'baz']);
    }

    public function testOffsetExists(): void
    {
        self::assertTrue($this->configBag->offsetExists('test'));
    }

    public function testOffsetGet(): void
    {
        self::assertSame('foo', $this->configBag->offsetGet('test'));
    }

    public function testOffsetSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Impossible to call offsetSet() on a frozen ConfigBag.');

        $this->configBag->offsetSet('test', 'd');
    }

    public function testOffsetUnset(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Impossible to call offsetUnset() on a frozen ConfigBag.');

        $this->configBag->offsetUnset('test', 'd');
    }

    public function testCount(): void
    {
        self::assertSame(2, $this->configBag->count());
        self::assertCount(2, $this->configBag);
    }
}
