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

namespace Viserio\Component\Manager\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Manager\Tests\Fixture\TestManager;
use Viserio\Contract\Manager\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class AbstractManagerTest extends MockeryTestCase
{
    public function testDriver(): void
    {
        $manager = new TestManager([
            'viserio' => [
                'test' => [
                    'default' => 'test',
                    'drivers' => [
                        'test' => ['driver' => 'test'],
                        'config' => ['driver' => 'config'],
                        'value' => ['driver' => 'foo'],
                        'testmanager' => ['driver' => 'testmanager'],
                    ],
                ],
            ],
        ]);

        self::assertTrue($manager->getDriver('test'));
        self::assertEquals(['name' => 'config', 'driver' => 'config'], $manager->getDriver('config'));
        self::assertEquals(['name' => 'value', 'driver' => 'foo'], $manager->getDriver('value'));
        self::assertTrue($manager->hasDriver('value'));
        self::assertEquals([
            'test' => true,
            'config' => ['name' => 'config', 'driver' => 'config'],
            'value' => ['name' => 'value', 'driver' => 'foo'],
        ], $manager->getDrivers());

        self::assertInstanceOf('stdClass', $manager->getDriver('testmanager'));
    }

    public function testCustomDriver(): void
    {
        $manager = new TestManager([
            'viserio' => [
                'test' => [
                    'default' => 'test',
                    'drivers' => [
                        'custom' => [''],
                    ],
                ],
            ],
        ]);
        $manager->extend('custom', function () {
            return 'custom';
        });

        self::assertSame('custom', $manager->getDriver('custom'));
    }

    public function testDriverToThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver [dont] is not supported.');

        $manager = new TestManager([
            'viserio' => [
                'test' => [
                    'default' => 'test',
                    'drivers' => [],
                ],
            ],
        ]);
        $manager->getDriver('dont');
    }

    public function testCall(): void
    {
        $manager = new TestManager([
            'viserio' => [
                'test' => [
                    'default' => 'test',
                    'drivers' => [],
                ],
            ],
        ]);
        $manager->extend('call', function () {
            return new ArrayContainer([]);
        });
        $manager->setDefaultDriver('call');

        $driver = $manager->getDriver('call');

        self::assertInstanceOf(ArrayContainer::class, $driver);

        $manager->set('test', 'test');

        self::assertSame('test', $manager->get('test'));
    }

    public function testCustomDriverClosureBoundObjectIsCacheManager(): void
    {
        $manager = new TestManager([
            'viserio' => [
                'test' => [
                    'default' => __CLASS__,
                    'drivers' => [
                        __CLASS__ => [''],
                    ],
                ],
            ],
        ]);

        $driver = function () {
            return $this;
        };
        $manager->extend(__CLASS__, $driver);

        self::assertEquals($manager, $manager->getDriver(__CLASS__));
        self::assertTrue($manager->hasDriver(__CLASS__));
    }

    public function testGetDriverConfig(): void
    {
        $manager = new TestManager([
            'viserio' => [
                'test' => [
                    'default' => 'pdo',
                    'drivers' => [
                        'pdo' => [
                            'servers' => 'localhost',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertSame(['servers' => 'localhost', 'name' => 'pdo'], $manager->getDriverConfig('pdo'));
    }

    public function testDefaultDriver(): void
    {
        $manager = new TestManager([
            'viserio' => [
                'test' => [
                    'default' => 'example',
                    'drivers' => [],
                ],
            ],
        ]);

        self::assertSame('example', $manager->getDefaultDriver());

        $manager->setDefaultDriver('new');

        self::assertSame('new', $manager->getDefaultDriver());
    }
}
