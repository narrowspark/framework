<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Support\Tests\Fixture\TestConnectionManager;

class AbstractConnectionManagerTest extends TestCase
{
    use MockeryTrait;

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Connection [fail] not supported.
     */
    public function testConnectionToThrowRuntimeException()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')->once();

        $manager = new TestConnectionManager($config);
        $manager->connection('fail');
    }

    public function testConnection()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.default', '')
            ->andReturn('test');
        $config->shouldReceive('get')
            ->once()
            ->with('connection.connections', [])
            ->andReturn([
                'test' => [''],
            ]);

        $manager = new TestConnectionManager($config);

        self::assertTrue($manager->connection());
        self::assertTrue(is_array($manager->getConnections('class')));
    }

    public function testExtend()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.connections', [])
            ->andReturn([
                'test' => [''],
            ]);

        $manager = new TestConnectionManager($config);
        $manager->extend('test', function () {
            return new stdClass();
        });

        self::assertInstanceOf(stdClass::class, $manager->connection('test'));
    }

    public function testGetConfig()
    {
        $config = $this->mock(RepositoryContract::class);

        $manager = new TestConnectionManager($config);

        self::assertInstanceOf(RepositoryContract::class, $manager->getConfig());

        $manager->setConfig($config);

        self::assertInstanceOf(RepositoryContract::class, $manager->getConfig());
    }

    public function testGetConnectionConfig()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.connections', [])
            ->andReturn([
                'pdo' => [
                    'servers' => 'localhost',
                ],
            ]);

        $manager = new TestConnectionManager($config);

        self::assertTrue(is_array($manager->getConnectionConfig('pdo')));
    }

    public function testCall()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.default', '')
            ->andReturn('foo');
        $config->shouldReceive('get')
            ->once()
            ->with('connection.connections', [])
            ->andReturn(['foo' => ['driver']]);

        $manager = new TestConnectionManager($config);

        self::assertSame([], $manager->getConnections());

        $return = $manager->getName();

        self::assertSame('manager', $return);
        self::assertArrayHasKey('foo', $manager->getConnections());
        self::assertTrue($manager->hasConnection('foo'));
    }

    public function testDefaultConnection()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connection.default', '')
            ->andReturn('example');

        $manager = new TestConnectionManager($config);

        self::assertSame('example', $manager->getDefaultConnection());

        $config->shouldReceive('set')
            ->once()
            ->with('connection.default', 'new');
        $manager->setDefaultConnection('new');
        $config->shouldReceive('get')
            ->once()
            ->with('connection.default', '')
            ->andReturn('new');

        self::assertSame('new', $manager->getDefaultConnection());
    }

    public function testExtensionsConnection()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->twice()
            ->with('connection.connections', [])
            ->andReturn([
                'stdclass2' => [
                    'servers' => 'localhost',
                ],
            ]);

        $manager = new TestConnectionManager($config);
        $manager->extend('stdclass2', function ($options) {
            return new stdClass();
        });

        self::assertTrue($manager->hasConnection('stdclass2'));
        self::assertInstanceOf(stdClass::class, $manager->connection('stdclass2'));

        $manager->reconnect('stdclass2');

        self::assertInstanceOf(stdClass::class, $manager->connection('stdclass2'));
    }
}
