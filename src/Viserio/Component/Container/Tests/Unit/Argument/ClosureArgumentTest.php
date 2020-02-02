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
     *
     * @param array $value
     * @param bool  $error
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
