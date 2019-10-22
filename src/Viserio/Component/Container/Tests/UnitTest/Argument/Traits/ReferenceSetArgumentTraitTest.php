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

namespace Viserio\Component\Container\Tests\UnitTest\Argumen\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Argument\ArrayArgument;
use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class ReferenceSetArgumentTraitTest extends TestCase
{
    /**
     * @dataProvider provideSetValueCases
     *
     * @param \Viserio\Contract\Container\Argument\Argument $object
     * @param array                                         $value
     * @param bool                                          $error
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

    public function provideSetValueCases(): iterable
    {
        return [
            [new ArrayArgument([]), [new ReferenceDefinition('foo')], false],
            [new IteratorArgument([]), [new ReferenceDefinition('foo')], false],
            [new IteratorArgument([]), ['string'], true],
        ];
    }
}
