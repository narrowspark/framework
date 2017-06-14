<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Sftp\SftpAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\SftpConnector;

class SftpConnectorTest extends TestCase
{
    public function testConnect()
    {
        $connector = new SftpConnector();

        $return = $connector->connect([
            'host'     => 'sftp.example.com',
            'port'     => 22,
            'username' => 'your-username',
            'password' => 'your-password',
        ]);

        self::assertInstanceOf(SftpAdapter::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires host configuration.
     */
    public function testConnectWithoutHost()
    {
        $connector = new SftpConnector();

        $connector->connect([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires port configuration.
     */
    public function testConnectWithoutPort()
    {
        $connector = new SftpConnector();

        $connector->connect([
            'host' => 'ftp.example.com',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires username configuration.
     */
    public function testConnectWithoutUsername()
    {
        $connector = new SftpConnector();

        $connector->connect([
            'host' => 'ftp.example.com',
            'port' => 21,
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires password or privateKey configuration.
     */
    public function testConnectWithoutPassword()
    {
        $connector = new SftpConnector();

        $connector->connect([
            'host'     => 'ftp.example.com',
            'port'     => 21,
            'username' => 'your-username',
        ]);
    }
}
