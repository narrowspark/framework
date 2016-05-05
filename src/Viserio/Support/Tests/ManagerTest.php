<?php
namespace Viserio\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\Tests\Fixture\TestManager;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testDefaultDriverSetGet()
    {
        $manager = new TestManager();
        $manager->setDefaultDriver('testDriver');

        $this->assertSame('testDriver', $manager->getDefaultDriver());
    }

    public function testConfigSetGet()
    {
        $config = $this->mock(ConfigContract::class);

        $manager = new TestManager();
        $manager->setConfig($config);

        $this->assertSame($config, $manager->getConfig());
    }

    public function testDriver()
    {
        $manager = new TestManager();
        $setting = ['name' => 'foo'];

        $this->assertTrue($manager->driver('test'));
        $this->assertEquals($setting, $manager->driver('config', $setting));
        $this->assertEquals($setting, $manager->driver('value', $setting));
        $this->assertEquals(['test' => true, 'config' => $setting, 'value' => $setting], $manager->getDrivers());
        $this->assertInstanceOf('Viserio\Support\Tests\Fixture\TestManager', $manager->driver('testmanager'));
    }

    public function testCustomeDriver()
    {
        $manager = new TestManager();
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
        (new TestManager())->driver('dont');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateDriverToThrowException()
    {
        (new TestManager())->driver('throw');
    }

    public function testCall()
    {
        $manager = new TestManager();
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
        $manager = new TestManager();

        $driver = function () {
            return $this;
        };
        $manager->extend(__CLASS__, $driver);

        $this->assertEquals($manager, $manager->driver(__CLASS__));
    }
}
