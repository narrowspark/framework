<?php
namespace Viserio\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\Tests\Fixture\TestManager;

class AbstractManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testDefaultDriverSetGet()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $manager->setDefaultDriver('testDriver');

        $this->assertSame('testDriver', $manager->getDefaultDriver());
    }

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
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $setting = ['name' => 'foo'];

        $this->assertTrue($manager->driver('test'));
        $this->assertEquals($setting, $manager->driver('config', $setting));
        $this->assertEquals($setting, $manager->driver('value', $setting));
        $this->assertEquals(['test' => true, 'config' => $setting, 'value' => $setting], $manager->getDrivers());
        $this->assertInstanceOf('stdClass', $manager->driver('testmanager'));
    }

    public function testCustomeDriver()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $manager->extend('custom', function () {
            return 'custom';
        });

        $this->assertSame('custom', $manager->driver('custom'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testDriverToThrowException()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $manager->driver('dont');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateDriverToThrowException()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $manager->driver('throw');
    }

    public function testCall()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);
        $manager->extend('call', function () {
            return new ArrayContainer();
        });
        $manager->setDefaultDriver('call');

        $driver = $manager->driver('call');

        $this->assertInstanceOf(ArrayContainer::class, $driver);
        $this->assertFalse($manager->has('test'));
    }

    public function testCustomDriverClosureBoundObjectIsCacheManager()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get');

        $manager = new TestManager($config);

        $driver = function () {
            return $this;
        };
        $manager->extend(__CLASS__, $driver);

        $this->assertEquals($manager, $manager->driver(__CLASS__));
    }
}
