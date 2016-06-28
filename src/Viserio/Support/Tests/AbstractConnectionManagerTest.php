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

        $factory = new TestConnectionManager($config);
        $factory->connection('fail');
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
            ->andReturn([]);

        $factory = new TestConnectionManager($config);

        $this->assertTrue($factory->connection());
        $this->assertTrue(is_array($factory->getConnections('class')));
    }

    public function testExtend()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once();

        $factory = new TestConnectionManager($config);
        $factory->extend('test', function() {
            return new stdClass();
        });

        $this->assertInstanceOf(stdClass::class, $factory->connection('test'));
    }
}
