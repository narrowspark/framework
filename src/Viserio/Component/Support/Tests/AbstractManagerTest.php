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

    public function testConfigSetGet()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $manager->setConfig($config);

        self::assertSame($config, $manager->getConfig());
    }

    public function testDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'test' => [''],
            ]);

        $manager = new TestManager($config);

        self::assertTrue($manager->driver('test'));

        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'config' => ['driver' => 'config'],
            ]);

        self::assertEquals(['name' => 'config', 'driver' => 'config'], $manager->driver('config'));

        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'value' => ['driver' => 'foo'],
            ]);

        self::assertEquals(['name' => 'value', 'driver' => 'foo'], $manager->driver('value'));
        self::assertTrue($manager->hasDriver('value'));
        self::assertEquals([
            'test'   => true,
            'config' => ['name' => 'config', 'driver' => 'config'],
            'value'  => ['name' => 'value', 'driver' => 'foo'],
        ], $manager->getDrivers());

        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'testmanager' => ['driver' => 'testmanager'],
            ]);

        self::assertInstanceOf('stdClass', $manager->driver('testmanager'));
    }

    public function testCustomeDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'custom' => [''],
            ]);

        $manager = new TestManager($config);
        $manager->extend('custom', function () {
            return 'custom';
        });

        self::assertSame('custom', $manager->driver('custom'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDriverToThrowException()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $manager->driver('dont');
    }

    public function testCall()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'call' => [''],
            ]);
        $config->shouldReceive('set')
            ->once()
            ->with('test.default', 'call');

        $manager = new TestManager($config);
        $manager->extend('call', function () {
            return new ArrayContainer();
        });
        $manager->setDefaultDriver('call');

        $config->shouldReceive('get')
            ->once()
            ->with('test.default', '')
            ->andReturn('call');

        $driver = $manager->driver('call');

        self::assertInstanceOf(ArrayContainer::class, $driver);
        self::assertFalse($manager->has('test'));
    }

    public function testCustomDriverClosureBoundObjectIsCacheManager()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                __CLASS__ => [''],
            ]);

        $manager = new TestManager($config);

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
        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'pdo' => [
                    'servers' => 'localhost',
                ],
            ]);

        $manager = new TestManager($config);

        self::assertTrue(is_array($manager->getDriverConfig('pdo')));
    }

    public function testDefaultDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('test.default', '')
            ->andReturn('example');

        $manager = new TestManager($config);

        self::assertSame('example', $manager->getDefaultDriver());

        $config->shouldReceive('set')
            ->once()
            ->with('test.default', 'new');
        $manager->setDefaultDriver('new');
        $config->shouldReceive('get')
            ->once()
            ->with('test.default', '')
            ->andReturn('new');

        self::assertSame('new', $manager->getDefaultDriver());
    }
}
