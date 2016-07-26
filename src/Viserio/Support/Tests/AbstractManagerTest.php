<?php
declare(strict_types=1);
namespace Viserio\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\Tests\Fixture\TestManager;

class AbstractManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testConfigSetGet()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $manager->setConfig($config);

        $this->assertSame($config, $manager->getConfig());
    }

    public function testDriver()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'test' => [''],
            ]);

        $manager = new TestManager($config);

        $this->assertTrue($manager->driver('test'));

        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'config' => ['driver' => 'config'],
            ]);

        $this->assertEquals(['name' => 'config', 'driver' => 'config'], $manager->driver('config'));

        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'value' => ['driver' => 'foo'],
            ]);

        $this->assertEquals(['name' => 'value', 'driver' => 'foo'], $manager->driver('value'));
        $this->assertTrue($manager->hasDriver('value'));
        $this->assertEquals([
            'test' => true,
            'config' => ['name' => 'config', 'driver' => 'config'],
            'value' => ['name' => 'value', 'driver' => 'foo'],
        ], $manager->getDrivers());

        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'testmanager' => ['driver' => 'testmanager'],
            ]);

        $this->assertInstanceOf('stdClass', $manager->driver('testmanager'));
    }

    public function testCustomeDriver()
    {
        $config = $this->mock(ConfigContract::class);
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

        $this->assertSame('custom', $manager->driver('custom'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDriverToThrowException()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $manager->driver('dont');
    }

    public function testCall()
    {
        $config = $this->mock(ConfigContract::class);
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

        $this->assertInstanceOf(ArrayContainer::class, $driver);
        $this->assertFalse($manager->has('test'));
    }

    public function testCustomDriverClosureBoundObjectIsCacheManager()
    {
        $config = $this->mock(ConfigContract::class);
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

        $this->assertEquals($manager, $manager->driver(__CLASS__));
        $this->assertTrue($manager->hasDriver(__CLASS__));
    }

    public function testGetDriverConfig()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('test.drivers', [])
            ->andReturn([
                'pdo' => [
                    'servers' => 'localhost',
                ],
            ]);

        $manager = new TestManager($config);

        $this->assertTrue(is_array($manager->getDriverConfig('pdo')));
    }

    public function testDefaultDriver()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('test.default', '')
            ->andReturn('example');

        $manager = new TestManager($config);

        $this->assertSame('example', $manager->getDefaultDriver());

        $config->shouldReceive('set')
            ->once()
            ->with('test.default', 'new');
        $manager->setDefaultDriver('new');
        $config->shouldReceive('get')
            ->once()
            ->with('test.default', '')
            ->andReturn('new');

        $this->assertSame('new', $manager->getDefaultDriver());
    }
}
