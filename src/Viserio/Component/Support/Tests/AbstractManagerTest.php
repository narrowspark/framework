<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Support\Tests\Fixture\TestManager;

class AbstractManagerTest extends TestCase
{
    use MockeryTrait;

    public function testDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'test',
                    'drivers' => [
                        'test'        => ['driver' => 'test'],
                        'config'      => ['driver' => 'config'],
                        'value'       => ['driver' => 'foo'],
                        'testmanager' => ['driver' => 'testmanager'],
                    ],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));

        self::assertTrue($manager->driver('test'));

        self::assertEquals(['name' => 'config', 'driver' => 'config'], $manager->driver('config'));

        self::assertEquals(['name' => 'value', 'driver' => 'foo'], $manager->driver('value'));
        self::assertTrue($manager->hasDriver('value'));
        self::assertEquals([
            'test'   => true,
            'config' => ['name' => 'config', 'driver' => 'config'],
            'value'  => ['name' => 'value', 'driver' => 'foo'],
        ], $manager->getDrivers());

        self::assertInstanceOf('stdClass', $manager->driver('testmanager'));
    }

    public function testCustomeDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'test',
                    'drivers' => [
                        'custom' => [''],
                    ],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));
        $manager->extend('custom', function () {
            return 'custom';
        });

        self::assertSame('custom', $manager->driver('custom'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Driver [dont] not supported.
     */
    public function testDriverToThrowException()
    {
        $manager = new TestManager(new ArrayContainer([
            'config' => [
                'viserio' => [
                    'test' => [
                        'default' => 'test',
                        'drivers' => [],
                    ],
                ],
            ],
        ]));
        $manager->driver('dont');
    }

    public function testCall()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'test',
                    'drivers' => [
                        'call' => [''],
                    ],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));
        $manager->extend('call', function () {
            return new ArrayContainer();
        });
        $manager->setDefaultDriver('call');

        $driver = $manager->driver('call');

        self::assertInstanceOf(ArrayContainer::class, $driver);
        self::assertFalse($manager->has('test'));
    }

    public function testCustomDriverClosureBoundObjectIsCacheManager()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => __CLASS__,
                    'drivers' => [
                        __CLASS__ => [''],
                    ],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));

        $driver = function () {
            return $this;
        };
        $manager->extend(__CLASS__, $driver);

        self::assertEquals($manager, $manager->driver(__CLASS__));
        self::assertTrue($manager->hasDriver(__CLASS__));
    }

    public function testGetDriverConfig()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'pdo',
                    'drivers' => [
                        'pdo' => [
                            'servers' => 'localhost',
                        ],
                    ],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));

        self::assertTrue(is_array($manager->getDriverConfig('pdo')));
    }

    public function testDefaultDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'example',
                    'drivers' => [],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));

        self::assertSame('example', $manager->getDefaultDriver());

        $manager->setDefaultDriver('new');

        self::assertSame('new', $manager->getDefaultDriver());
    }
}
