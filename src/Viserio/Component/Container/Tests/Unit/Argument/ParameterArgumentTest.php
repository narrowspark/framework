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

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Argument\ParameterArgument;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Argument\ParameterArgument
 *
 * @small
 */
final class ParameterArgumentTest extends TestCase
{
    /** @var \Viserio\Component\Container\Argument\ParameterArgument */
    protected $argument;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->argument = new ParameterArgument('test', false);
    }

    public function testParameterValueCantBeEmptyOnConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The [$parameter] must be a non-empty string.');

        new ParameterArgument('', '');
    }

    /**
     * @dataProvider provideSetAndGetValueCases
     *
     * @param array $value
     * @param int   $error
     */
    public function testSetAndGetValue(array $value, int $error = 0): void
    {
        if ($error === 1) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('A [Viserio\Component\Container\Argument\ParameterArgument] must hold parameter name and default value as fallback if parameter doesn\'t exist.');
        } elseif ($error === 2) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('The first array value must be a non-empty string.');
        }

        $this->argument->setValue($value);

        self::assertSame($value, $this->argument->getValue());
    }

    public static function provideSetAndGetValueCases(): iterable
    {
        return [
            [['string'], 1],
            [['', 2], 2],
            [['test', 2], 0],
        ];
    }
}
