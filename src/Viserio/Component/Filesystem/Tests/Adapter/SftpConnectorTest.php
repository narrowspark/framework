<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Sftp\SftpAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\SftpConnector;

/**
 * @internal
 */
final class SftpConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $connector = new SftpConnector();

        $return = $connector->connect([
            'host'     => 'sftp.example.com',
            'port'     => 22,
            'username' => 'your-username',
            'password' => 'your-password',
        ]);

        $this->assertInstanceOf(SftpAdapter::class, $return);
    }

    public function testConnectWithoutHost(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The sftp connector requires host configuration.');

        $connector = new SftpConnector();

        $connector->connect([]);
    }

    public function testConnectWithoutPort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The sftp connector requires port configuration.');

        $connector = new SftpConnector();

        $connector->connect([
            'host' => 'ftp.example.com',
        ]);
    }

    public function testConnectWithoutUsername(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The sftp connector requires username configuration.');

        $connector = new SftpConnector();

        $connector->connect([
            'host' => 'ftp.example.com',
            'port' => 21,
        ]);
    }

    public function testConnectWithoutPassword(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The sftp connector requires password or privateKey configuration.');

        $connector = new SftpConnector();

        $connector->connect([
            'host'     => 'ftp.example.com',
            'port'     => 21,
            'username' => 'your-username',
        ]);
    }
}
