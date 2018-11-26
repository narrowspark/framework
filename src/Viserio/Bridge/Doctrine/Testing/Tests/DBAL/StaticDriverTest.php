<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Tests\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\Testing\DBAL\StaticConnection;
use Viserio\Bridge\Doctrine\Testing\DBAL\StaticDriver;
use Viserio\Bridge\Doctrine\Testing\Tests\Fixtures\MockDriver;

/**
 * @internal
 */
final class StaticDriverTest extends TestCase
{
    /**
     * @var AbstractPlatform|\PHPUnit_Framework_MockObject_MockObject
     */
    private $platform;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testReturnCorrectPlatform(): void
    {
        $driver = new StaticDriver(new MockDriver(), $this->platform);

        $this->assertSame($this->platform, $driver->getDatabasePlatform());
        $this->assertSame($this->platform, $driver->createDatabasePlatformForVersion(1));
    }

    public function testConnect(): void
    {
        $driver = new StaticDriver(new MockDriver(), $this->platform);
        $driver::setKeepStaticConnections(true);

        $connection1 = $driver->connect(['database_name' => 1], 'user1', 'pw1');
        $connection2 = $driver->connect(['database_name' => 2], 'user1', 'pw2');

        $this->assertInstanceOf(StaticConnection::class, $connection1);
        $this->assertNotSame($connection1->getWrappedConnection(), $connection2->getWrappedConnection());

        $driver         = new StaticDriver(new MockDriver(), $this->platform);
        $connectionNew1 = $driver->connect(['database_name' => 1], 'user1', 'pw1');
        $connectionNew2 = $driver->connect(['database_name' => 2], 'user1', 'pw2');

        $this->assertSame($connection1->getWrappedConnection(), $connectionNew1->getWrappedConnection());
        $this->assertSame($connection2->getWrappedConnection(), $connectionNew2->getWrappedConnection());
    }
}
