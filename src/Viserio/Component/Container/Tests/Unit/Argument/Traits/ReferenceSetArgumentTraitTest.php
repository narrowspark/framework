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

namespace Viserio\Component\Container\Tests\Unit\Argumen\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Argument\ArrayArgument;
use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Argument\ArrayArgument
 * @covers \Viserio\Component\Container\Argument\IteratorArgument
 * @covers \Viserio\Component\Container\Argument\Traits\ReferenceSetArgumentTrait
 *
 * @small
 */
final class ReferenceSetArgumentTraitTest extends TestCase
{
    /**
     * @dataProvider provideSetValueCases
     */
    public function testSetValue(ArgumentContract $object, array $value, bool $error): void
    {
        if ($error) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage(\sprintf('A [%s] must hold only Reference instances, [string] given.', \get_class($object)));
        }

        $object->setValue($value);

        self::assertSame($value, $object->getValue());
    }

    public static function provideSetValueCases(): iterable
    {
        return [
            [new ArrayArgument([]), [new ReferenceDefinition('foo')], false],
            [new IteratorArgument([]), [new ReferenceDefinition('foo')], false],
            [new IteratorArgument([]), ['string'], true],
        ];
    }
}
