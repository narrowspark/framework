<?php
namespace Viserio\Support\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use stdClass;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\Tests\Fixture\TestConnectionManager;

class AbstractConnectionManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The connection [fail] is not supported.
     */
    public function testConnectionToThrowRuntimeException()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')->never();

        $manager = new TestConnectionManager($config);
        $manager->connection('fail');
    }

    public function testConnection()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.default', '')
            ->andReturn('test');
        $config->shouldReceive('get')
            ->once()
            ->with('connection.connections', [])
            ->andReturn([
                'test' => ['']
            ]);

        $manager = new TestConnectionManager($config);

        $this->assertTrue($manager->connection());
        $this->assertTrue(is_array($manager->getConnections('class')));
    }

    public function testExtend()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.connections', [])
            ->andReturn([
                'test' => ['']
            ]);

        $manager = new TestConnectionManager($config);
        $manager->extend('test', function() {
            return new stdClass();
        });

        $this->assertInstanceOf(stdClass::class, $manager->connection('test'));
    }

    public function testGetConfig()
    {
        $config = $this->mock(ConfigContract::class);

        $manager = new TestConnectionManager($config);

        $this->assertInstanceOf(ConfigContract::class, $manager->getConfig());

        $manager->setConfig($config);

        $this->assertInstanceOf(ConfigContract::class, $manager->getConfig());
    }

    public function testGetConnectionConfig()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.connections', [])
            ->andReturn([
                'pdo' => [
                    'servers' => 'localhost',
                ]
            ]);

        $manager = new TestConnectionManager($config);

        $this->assertTrue(is_array($manager->getConnectionConfig('pdo')));
    }

    public function testCall()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.default', '')
            ->andReturn('foo');
        $config->shouldReceive('get')
            ->once()
            ->with('connection.connections', [])
            ->andReturn(['foo' => ['driver']]);

        $manager = new TestConnectionManager($config);

        $this->assertSame([], $manager->getConnections());

        $return = $manager->getName();

        $this->assertSame('manager', $return);
        $this->assertArrayHasKey('foo', $manager->getConnections());
    }

    public function testDefaultConnection()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.default', '')
            ->andReturn('example');

        $manager = new TestConnectionManager($config);

        $this->assertSame('example', $manager->getDefaultConnection());

        $config->shouldReceive('set')
            ->once()
            ->with('connection.default', 'new');
        $manager->setDefaultConnection('new');
        $config->shouldReceive('get')
            ->once()
            ->with('connection.default', '')
            ->andReturn('new');

        $this->assertSame('new', $manager->getDefaultConnection());
    }

    public function testExtensionsConnection()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->twice()
            ->with('connection.connections', [])
            ->andReturn([
                'stdclass2' => [
                    'servers' => 'localhost',
                ]
            ]);

        $factory = new TestConnectionManager($config);
        $factory->extend('stdclass2', function($options) {
            return new stdClass;
        });

        $this->assertInstanceOf(stdClass::class, $factory->connection('stdclass2'));

        $factory->reconnect('stdclass2');

        $this->assertInstanceOf(stdClass::class, $factory->connection('stdclass2'));
    }
}
