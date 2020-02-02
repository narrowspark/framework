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

namespace Viserio\Component\Container\Tests\Unit\Argument;

use Closure;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Argument\ConditionArgument;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Argument\ConditionArgument
 *
 * @small
 */
final class ConditionArgumentTest extends TestCase
{
    /** @var \Viserio\Component\Container\Argument\ConditionArgument */
    protected $argument;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->argument = new ConditionArgument([], function (): void {
        });
    }

    public function testGetCallback(): void
    {
        self::assertInstanceOf(Closure::class, $this->argument->getCallback());
    }

    /**
     * @dataProvider provideSetAndGetValueCases
     *
     * @param array $value
     * @param bool  $error
     */
    public function testSetAndGetValue(array $value, bool $error): void
    {
        if ($error) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('A [Viserio\Component\Container\Argument\ConditionArgument] must hold only strings and references, [integer] given.');
        }

        $this->argument->setValue($value);

        self::assertSame($value, $this->argument->getValue());
    }

    public static function provideSetAndGetValueCases(): iterable
    {
        return [
            [['string'], false],
            [[1], true],
        ];
    }
}
