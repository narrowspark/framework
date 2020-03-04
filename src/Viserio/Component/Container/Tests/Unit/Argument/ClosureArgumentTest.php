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

namespace Viserio\Component\Container\Tests\Unit\Argument;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Argument\ClosureArgument;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Argument\ClosureArgument
 *
 * @small
 */
final class ClosureArgumentTest extends TestCase
{
    /** @var \Viserio\Component\Container\Argument\ClosureArgument */
    protected $argument;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->argument = new ClosureArgument(new ReferenceDefinition('foo'));
    }

    /**
     * @dataProvider provideSetValueCases
     */
    public function testSetValue(array $value, bool $error): void
    {
        if ($error) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('A [Viserio\Component\Container\Argument\ClosureArgument] must hold one and only one reference.');
        }

        $this->argument->setValue($value);

        self::assertSame($value, $this->argument->getValue());
    }

    public static function provideSetValueCases(): iterable
    {
        return [
            [[new ReferenceDefinition('bar')], false],
            [[1], true],
        ];
    }
}
