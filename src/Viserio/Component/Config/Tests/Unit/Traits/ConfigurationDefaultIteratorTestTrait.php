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

namespace Viserio\Component\Config\Tests\Unit\Traits;

use ArrayIterator;
use IteratorIterator;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultConfigConfiguration;
use Viserio\Component\Config\Tests\Fixture\PackageDefaultConfigComponentConfiguration;

/**
 * @method IteratorIterator getConfigurationIterator(string $class, ArrayIterator $iterator, ?string $id = null)
 */
trait ConfigurationDefaultIteratorTestTrait
{
    public function testDefaultConfigResolvingShouldReturnDefaultConfigIfEmptyConfigurationIsProvided(): void
    {
        $expected = [
            'minLength' => 2,
            'maxLength' => 10,
        ];
        $iterator = $this->getConfigurationIterator(
            PackageDefaultConfigComponentConfiguration::class,
            new ArrayIterator([])
        );

        self::assertCount(2, $iterator);
        self::assertSame($expected, \iterator_to_array($iterator));
    }

    public function testDefaultConfigResolving(): void
    {
        $iterator = $this->getConfigurationIterator(
            ConnectionDefaultConfigConfiguration::class,
            new ArrayIterator([])
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

    public function testDefaultConfigResolvingShouldWorkWithEmptyArray(): void
    {
        $iterator = $this->getConfigurationIterator(
            ConnectionComponentDefaultConfigConfiguration::class,
            new ArrayIterator([])
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

    public static function provideConfigurationDefaultIteratorConfigCases(): iterable
    {
        $config = require dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'testing.config.php';

        return [
            [
                $config,
            ],
        ];
    }
}
