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

namespace Viserio\Component\Config\Tests\Unit;

use ArrayIterator;
use IteratorIterator;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ConfigurationIterator;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultConfigConfiguration;
use Viserio\Component\Config\Tests\Fixture\PlainComponentConfiguration;
use Viserio\Component\Config\Tests\Unit\Traits\ConfigurationDefaultIteratorTestTrait;
use Viserio\Component\Config\Tests\Unit\Traits\ConfigurationDeprecatedIteratorTestTrait;
use Viserio\Component\Config\Tests\Unit\Traits\ConfigurationDimensionsIteratorTestTrait;
use Viserio\Component\Config\Tests\Unit\Traits\ConfigurationValidatorIteratorTestTrait;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\ConfigurationIterator
 */
final class ConfigurationIteratorTest extends TestCase
{
    use ConfigurationDimensionsIteratorTestTrait;
    use ConfigurationDefaultIteratorTestTrait;
    use ConfigurationValidatorIteratorTestTrait;
    use ConfigurationDeprecatedIteratorTestTrait;

    public static function provideDeprecationMessageResolvingCases(): iterable
    {
        return \array_merge(
            (array) ConfigurationDeprecatedIteratorTestTrait::provideDeprecationMessageResolvingCases(),
            [
                'It ignores deprecation if interface is not added' => [
                    PlainComponentConfiguration::class,
                    null,
                ],
            ],
        );
    }

    public function testTransformArrayToIterator(): void
    {
        $iterator = new ConfigurationIterator(
            ConnectionDefaultConfigConfiguration::class,
            []
        );

        $array = \iterator_to_array($iterator);

        self::assertCount(1, $array);
        self::assertArrayHasKey('params', $array);
        self::assertSame(
            $array['params']['host'],
            'awesomehost'
        );
        self::assertSame(
            $array['params']['port'],
            '4444'
        );
    }

    /**
     * @param string        $class
     * @param ArrayIterator $iterator
     * @param null|string   $id
     *
     * @return IteratorIterator
     */
    protected function getConfigurationIterator(string $class, $iterator, ?string $id = null): IteratorIterator
    {
        return new ConfigurationIterator($class, $iterator, $id);
    }
}
