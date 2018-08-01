<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Support\Exception\InvalidArgumentException;
use Viserio\Component\Support\Tests\Fixture\TestManager;

/**
 * @internal
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
                        'test'        => ['driver' => 'test'],
                        'config'      => ['driver' => 'config'],
                        'value'       => ['driver' => 'foo'],
                        'testmanager' => ['driver' => 'testmanager'],
                    ],
                ],
            ],
        ]);

        static::assertTrue($manager->getDriver('test'));
        static::assertEquals(['name' => 'config', 'driver' => 'config'], $manager->getDriver('config'));
        static::assertEquals(['name' => 'value', 'driver' => 'foo'], $manager->getDriver('value'));
        static::assertTrue($manager->hasDriver('value'));
        static::assertEquals([
            'test'   => true,
            'config' => ['name' => 'config', 'driver' => 'config'],
            'value'  => ['name' => 'value', 'driver' => 'foo'],
        ], $manager->getDrivers());

        static::assertInstanceOf('stdClass', $manager->getDriver('testmanager'));
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

        static::assertSame('custom', $manager->getDriver('custom'));
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

        static::assertInstanceOf(ArrayContainer::class, $driver);

        $manager->set('test', 'test');

        static::assertSame('test', $manager->get('test'));
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

        static::assertEquals($manager, $manager->getDriver(__CLASS__));
        static::assertTrue($manager->hasDriver(__CLASS__));
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

        static::assertSame(['servers' => 'localhost', 'name' => 'pdo'], $manager->getDriverConfig('pdo'));
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

        static::assertSame('example', $manager->getDefaultDriver());

        $manager->setDefaultDriver('new');

        static::assertSame('new', $manager->getDefaultDriver());
    }
}
